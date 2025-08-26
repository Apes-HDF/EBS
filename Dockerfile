# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact

ARG PHP_VERSION=8.2
ARG CADDY_VERSION=2.10.0

# yarn build
FROM node AS yarn_build
WORKDIR /usr/app
RUN apt-get update && apt-get install tar
RUN mkdir -p /usr/app/vendor/symfony
RUN curl -L https://github.com/symfony/ux-autocomplete/archive/v2.7.1.tar.gz -o ux-autocomplete.tar.gz
RUN tar -xzvf ux-autocomplete.tar.gz --directory /usr/app/vendor/symfony
RUN mv /usr/app/vendor/symfony/ux-autocomplete-2.7.1 /usr/app/vendor/symfony/ux-autocomplete
COPY package.json yarn.lock .
RUN yarn install
COPY . .
RUN yarn build

# Prod image
FROM php:${PHP_VERSION}-fpm-alpine AS app_php

# needed for security update until base image is updated
#RUN apk upgrade libcurl curl openssl openssl-dev libressl libcrypto3 libssl3

# Allow to use development versions of Symfony
ARG STABILITY="stable"
ENV STABILITY ${STABILITY}

# Allow to select Symfony version
ARG SYMFONY_VERSION=""
ENV SYMFONY_VERSION ${SYMFONY_VERSION}

ENV APP_ENV=prod

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
    	nghttp2 \
        libcrypto3 \
        libssl3 \
	;

RUN set -eux; \
    install-php-extensions \
    	intl \
    	zip \
    	apcu \
		opcache \
    	xsl \
    	redis \
    	bcmath \
    ;

###> doctrine/doctrine-bundle ###
RUN apk add --no-cache --virtual .pgsql-deps postgresql-dev; \
	docker-php-ext-install -j$(nproc) pdo_pgsql; \
	apk add --no-cache --virtual .pgsql-rundeps so:libpq.so.5; \
	apk del .pgsql-deps
###< doctrine/doctrine-bundle ###
###< recipes ###

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN mkdir -p /var/run/php

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN chown -R www-data: /srv/app/ /var/run/php/

# prevent the reinstallation of vendors at every changes in the source code
USER www-data
COPY --chown=www-data:www-data composer.* symfony.* ./
RUN set -eux; \
    if [ -f composer.json ]; then \
		composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
		composer clear-cache; \
    fi

# copy sources
COPY --chown=www-data:www-data . .
RUN rm -Rf docker/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	if [ -f composer.json ]; then \
		composer dump-autoload --classmap-authoritative --no-dev; \
		composer dump-env prod; \
		COMPOSER_MEMORY_LIMIT=-1 composer run-script --no-dev post-install-cmd; \
		chmod +x bin/console; sync; \
	fi;

# copy yarn build output
COPY --from=yarn_build --chown=www-data:www-data /usr/app/public/build/ public/build/

# Dev image
FROM app_php AS app_php_dev

USER root

###> recipes ###
###> symfony/panther ###
# Chromium and ChromeDriver
ENV PANTHER_NO_SANDBOX 1
# Not mandatory, but recommended
ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'
RUN apk add --no-cache chromium chromium-chromedriver

# Firefox and geckodriver
#ARG GECKODRIVER_VERSION=0.29.0
#RUN apk add --no-cache firefox
#RUN wget -q https://github.com/mozilla/geckodriver/releases/download/v$GECKODRIVER_VERSION/geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz; \
#	tar -zxf geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz -C /usr/bin; \
#	rm geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz
###< symfony/panther ###


# Additional dev tools (graphviz is to have the dot program to generate the workflows' graphs)
RUN apk add --no-cache \
    	make \
    	nano \
    	vim \
    	neovim \
    	graphviz \
	;

# Load aliases at interactive shell (sh -l)
ENV ENV="/etc/profile"

ENV APP_ENV=dev XDEBUG_MODE=off
USER www-data
# We must create directories to avoid problems with EasyAdmin which checks rights
# even a cloud storgae is used (thoses directories will stay empty when a cloud
# storage is used).
RUN mkdir -p /srv/app/public/storage/uploads/category
RUN mkdir -p /srv/app/public/storage/uploads/menu
RUN mkdir -p /srv/app/public/storage/uploads/product
RUN mkdir -p /srv/app/public/storage/uploads/user

USER root
VOLUME /srv/app/var/

RUN rm $PHP_INI_DIR/conf.d/app.prod.ini; \
	mv "$PHP_INI_DIR/php.ini" "$PHP_INI_DIR/php.ini-production"; \
	mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/

RUN set -eux; \
	install-php-extensions xdebug

RUN rm -f .env.local.php

# Build Caddy with the Mercure and Vulcain modules
FROM caddy:${CADDY_VERSION}-builder-alpine AS app_caddy_builder

# Temporary fix for https://github.com/dunglas/mercure/issues/770
# https://github.com/dunglas/symfony-docker/pull/407/files

# FROM caddy:2.9.1-builder-alpine AS app_caddy_builder


# RUN xcaddy build \
#	--with github.com/dunglas/mercure \
#	--with github.com/dunglas/mercure/caddy \
#	--with github.com/dunglas/vulcain \
#	--with github.com/dunglas/vulcain/caddy

RUN xcaddy build \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain/caddy

# Caddy image
FROM caddy:${CADDY_VERSION} AS app_caddy

# needed for security update until base image is updated
#RUN apk upgrade libcurl curl openssl openssl-dev libressl libcrypto1.1 libssl1.1 libcrypto3 libssl3

WORKDIR /srv/app

COPY --from=app_caddy_builder /usr/bin/caddy /usr/bin/caddy
COPY --from=app_php /srv/app/public public/
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
