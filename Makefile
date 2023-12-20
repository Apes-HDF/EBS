# Config
CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

SHELL       = sh
REDIS_PORT  = 6389

# Executables (local)
DOCKER      = docker
DOCKER_COMP = docker compose
REDIS       = redis-cli

# Docker containers
PHP_CONT  = $(DOCKER_COMP) exec php

# see https://hub.docker.com/r/gmolaire/yarn
YARN_CONT = $(DOCKER) run -it --rm -w "/usr/app" -v "${PWD}":/usr/app gmolaire/yarn yarn

# Main executables
PHP          = $(PHP_CONT) php
COMPOSER     = $(PHP_CONT) composer
SYMFONY      = $(PHP_CONT) bin/console
PHPUNIT      = $(PHP_CONT) bin/phpunit

# Vendors executables
PHPSTAN      = $(PHP_CONT) ./vendor/bin/phpstan
PHP_CS_FIXER = $(PHP_CONT) ./vendor/bin/php-cs-fixer
TWIGCS       = $(PHP_CONT) ./vendor/bin/twigcs
RECTOR       = $(PHP_CONT) ./vendor/bin/rector

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc ci cs lint-twigcs

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 —————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up -d

wait: ## Waits for all containers to be ready
	@$(DOCKER_COMP) up --wait

start: ## Starts the main containers (load-fixtures must be done so the caddy service can be healthy)
start: up yarn-install load-fixtures yarn-dev wait

start-dev: ## Start additional and optional dev tools in the docker-compose.override.yml.dist file
	@$(DOCKER_COMP) -f docker-compose.override.yml.dist up --wait

stop: ## Stop and remove the docker containers (and volumes) of the project
	@$(DOCKER_COMP) down -v --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh -l

linux-fix-perms: ## Fix permissions on Linux
	$(PHP_CONT) chown -R $(CURRENT_UID):$(CURRENT_GID) .

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: composer.lock ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval env ?= 'dev')
	@$(eval c ?=)
	@$(SYMFONY) $(c) --env=$(env)

cc: c=c:c ## Clear the cache
cc: sf

purge: ## Purge the all vars files manually
	@$(PHP_CONT) sh -c "rm -rf /srv/app/var/*"

## —— Coding standards ✨ ——————————————————————————————————————————————————————
stan: ## Run PHPStan. Use the 'path' parameter to check a given file only: make stan path=src/Services/ControllerHelper.php
	$(eval path ?= )
	@$(PHPSTAN) analyse -c phpstan.neon --memory-limit 1G -vvv $(path)

stan-cc: ## Clear manually the PHPStan result cache
	@$(PHPSTAN) clear-result-cache

lint-php: ## Lint files with php-cs-fixer (just use fix-php)
	$(PHP_CS_FIXER) fix --dry-run --allow-risky=yes

lint-twig: ## Lint Twig files to check they are well formatted (no parse error)
	@$(SYMFONY) lint:twig --env=dev

lint-twigcs: ## Check Twig coding style
	@$(TWIGCS) templates/ --exclude vendor

lint-container: ## Check there is no problem with the container
	@$(SYMFONY) lint:container

lint-yaml: ## Check all YAML files are well formatted
	@$(SYMFONY) lint:yaml --parse-tags config/ translations/ templates/

fix-php: ## Fix files with php-cs-fixer
	@$(PHP_CS_FIXER) fix --allow-risky=yes

rector: ## Run rector with current rules in rector.php
	@$(RECTOR) process src/

ci: ## Run pre-commit checks to ensure the CI will be green
ci: cs lint-yaml lint-container lint-twig lint-twigcs yarn-lint doctrine-validate test-complete

cs: ## Run PHPStan and php-cs-fixer only
cs: fix-php stan

## —— Project ——————————————————————————————————————————————————————————————————
load-fixtures: drop-db ## Build the DB, control the schema validity, load fixtures and check the migration status (deb)
	$(eval env ?= 'dev')
	@$(SYMFONY) doctrine:database:create --if-not-exists --env=$(env)
	@$(SYMFONY) doctrine:schema:create --env=$(env)
	@$(SYMFONY) doctrine:schema:validate --env=$(env)
	@$(SYMFONY) doctrine:migrations:migrate --env=$(env) --no-interaction
	@$(SYMFONY) messenger:setup-transports --env=$(env)
	@$(SYMFONY) hautelook:fixtures:load --no-interaction -vv --no-bundles --env=$(env)
	@$(SYMFONY) app:index-products --env=$(env)

drop-db: ## Delete the whole database, useful when having integrity problems for data or constraints
	$(eval env ?= 'dev')
	@$(SYMFONY) doctrine:database:drop --env=$(env) --if-exists --force

load-test-fixtures: env=test ## Allows to use the test fixtures to debug problems
load-test-fixtures: load-fixtures

load-prod-fixtures: env=prod
load-prod-fixtures: load-fixtures ## Same than load-fixtures but with only minimum data

cache-clear: ## Clear the application cache (used for tests)
	$(eval env ?= 'dev')
	@$(SYMFONY) c:c --env=$(env)
	@$(SYMFONY) cache:pool:clear cache.app --env=$(env)

doctrine-validate: ## Validate the doctrine schema
	$(eval env ?= 'dev')
	@$(SYMFONY) doctrine:schema:validate --env=$(env)

doctrine-migrate: ## Run all the available Doctrine migrations
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

meilisearch-index:
	$(eval env ?= 'dev')
	@$(SYMFONY) app:index-products --env=$(env)


## —— Tests ✅ —————————————————————————————————————————————————————————————————
test: ## Run tests with optional suite, filter and options
	@$(eval testsuite ?= 'all')
	@$(eval filter ?= '.')
	@$(eval options ?= '--stop-on-failure')
	@$(PHPUNIT) --testsuite=$(testsuite) --filter=$(filter) $(options)

test-complete: ## Run all tests without stopping on the first error
test-complete: env=test
test-complete: options=
test-complete: cc load-fixtures test

test-debug: ## Run all tests in debug mode
test-debug: env=test
test-debug: options=--debug --stop-on-failure
test-debug: cc load-fixtures test

test-unit: ## Run unit tests only
test-unit: env=test
test-unit: testsuite=unit
test-unit: test

test-api: ## Run API tests only
test-api: env=test
test-api: testsuite=api
test-api: test

test-integration: ## Run integration tests only
test-integration: env=test
test-integration: testsuite=integration
test-integration: load-test-fixtures test

test-functional: ## Run functional tests only
test-functional: env=test
test-functional: testsuite=functional
test-functional: load-test-fixtures test

test-e2e: ## Run E2E tests only
test-e2e: env=test
test-e2e: load-test-fixtures test

coverage:  ## Generate the HTML PHPUnit code coverage report locally
coverage: env=test
coverage: load-test-fixtures
    # Cache must be generated by PHPUnit so it can run compiler passes
	@$(PHP_CONT) sh -c "rm -rf /srv/app/var/cache/test"
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage php php -d xdebug.enable=1 -d memory_limit=-1 bin/phpunit --coverage-html=docs/coverage

## —— Debug 🐞——————————————————————————————————————————————————————————————————
redis: ## Connect to redis with CLI (redis-cli must be installed locally)
	@$(REDIS) -p $(REDIS_PORT)

redis-cc: ## Flush all Redis cache
	@$(REDIS) -p $(REDIS_PORT) flushall

## —— Yarn 🐱 / JavaScript —————————————————————————————————————————————————————
yarn-install: ## Install node dependencies with Yarn
	@$(YARN_CONT) install

yarn-dev: ## Build the assets for the dev env
	@$(YARN_CONT) dev

yarn-lint: ## Lint JS files
	@$(YARN_CONT) lint

yarn-cmd: ## Run a given command
	@$(eval cmd ?= 'help')
	@$(YARN_CONT) $(cmd)

## —— Doc 📚 ———————————————————————————————————————————————————————————————————
workflows: ## Generate and update the graphs of all available workflows
	@$(PHP_CONT) bin/console workflow:dump service_request_status | dot -Tpng -o docs/service_request_status_workflow.png
	@echo "Done!"
