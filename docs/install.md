# Installation

This is an example of installation of the project on a virtual private server. 
I has been installed on a [OVH VPS](https://www.ovhcloud.com/fr/vps/), the smallest
one, its specifications are:

* 1 vCore
* 2 Go RAM
* 20 Go SSD SATA
* 100 Mbit/s unlimited
* Ubuntu 22.04 LTS

Of course any hoster and servers type can be used. 
Small modifications may be required depending on the used operating system.
But as the installation process is quite generic, ony small changes should be required
to fit your operating system needs.

Some links are provided for reference. 
Of course you are free to install the way you want the various required component
it's just a basic working example that can be adapted.

I havent's put sudo command, it depends on wheter you run an administrator account
or not.
If not, you will have to prefix some commands with sudo.


## Server update ([source](https://www.linuxstart.com/install-php-ubuntu/)):

First we must update the base packages of the server:

    apt update && sudo apt upgrade -y
    apt-get install linux-headers-generic make
    snap install nvim --classic
    ([ -f /var/run/reboot-required ] && sudo reboot -f)


## PHP Installation

    apt install php php-cli php-common php-mbstring php-xmlrpc php-soap php-gd php-xml php-intl php-mysql php-cli php-zip php-curl php-apcu php-redis -y

Verify the cli:

    php --version

We should have the following output:

    PHP 8.1.2-1ubuntu2.11 (cli) (built: Feb 22 2023 22:56:18) (NTS)
    Copyright (c) The PHP Group
    Zend Engine v4.1.2, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.2-1ubuntu2.11, Copyright (c), by Zend Technologies

Here the defautl PHP version is used but it is better to use the `ondrej/php` PPA
repository to have the last minor PHP version.


## Apache installation ([source](https://petri.com/install-apache-ubuntu-linux/))

    apt install apache2
    systemctl start apache2
    systemctl enable apache2
    service apache2 status

It should display (green ●):

    ● apache2.service - The Apache HTTP Server

And we should see the default Apache page when accessing the server with HTTP:

![Apache2](images/apache2-default-page.png "Apache2")

(eg: http://vps-9cd99999.vps.ovh.net)



## Generate a SSH key ([source](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent))

This section is only needed if you want to fetch the project from a private repository.
You may also not need this depending of your deployment process.

    ssh-keygen -t ed25519 -C "ubuntu@vps-6cd61302"
    eval `ssh-agent -s`
    ssh-add


## Cloning the project

Git is already installed on this distribution.
You must install it if it isn't available.

    cd /var/www
    git clone git@github.com:ApesHDF/EBS.git


## Composer ([source](https://getcomposer.org/download/))
    
Follow the instructions on https://getcomposer.org/download/. 

Then should have composer available

    composer --version

Which gives:

    Composer version 2.5.5 2023-03-21 11:50:05

Install the project dependencies:

    cd /var/www/plateforme-ebs
    composer install --ignore-platform-req=php

The `--ignore-platform-` options is required if you have a PHP version below `8.1.11`.


## Redis installation

     apt-get install redis


## Meilisearch [cloud service](https://cloud.meilisearch.com)

You can install a Meilsearch local instance but you can also use the cloud free
service.
It allows to have 100k documents and 10k search by month.
It works well for testing purpose.
Then can you always install a local Meilisearch instance when you reached the free
tier threshold.

Once you cloud instance is created, get the instance URL and the password.
You have to use them for the `MEILISEARCH_URL` and `MEILISEARCH_API_KEY` variables 
(check below).


## Postgres 14 installation ([source](https://computingforgeeks.com/install-postgresql-14-on-ubuntu-jammy-jellyfish/))

    apt install vim curl wget gpg gnupg2 software-properties-common apt-transport-https lsb-release ca-certificates
    apt install postgresql-14 postgresql-client
    systemctl status postgresql@14-main.service
    sudo -u postgres psql -c "SELECT version();"

### Create the database

    sudo -u postgres psql
    create database app;
    create user app with encrypted password '!ChangeMe!';
    grant all privileges on database app to app;

### Create the tables and load the initial fixtures

    bin/console doctrine:schema:create --env=prod
    bin/console doctrine:migrations:migrate --env=prod --no-interaction
    bin/console messenger:setup-transports --env=prod
    bin/console hautelook:fixtures:load --no-interaction -vv --no-bundles --env=prod

##  Apache configuration

Here is a simple vhost:

    nvim /etc/apache2/sites-available/000-default.conf

And put:

    <VirtualHost *:80>
        ServerAdmin admin@example.com
        DocumentRoot /var/www/plateforme-ebs/public

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory /var/www/plateforme-ebs/public>
             AllowOverride All
             Require all granted
             FallbackResource /index.php
         </Directory>
    </VirtualHost>

Of course you should install a SSL certificate later with [letsencrypt](https://letsencrypt.org/)
for example.


## Node and Yarn installation

Here are some resources:

* https://www.howtoforge.com/how-to-install-yarn-on-ubuntu-22-04/
* https://linuxize.com/post/how-to-install-node-js-on-ubuntu-22-04/
* https://www.digitalocean.com/community/tutorials/how-to-install-and-use-the-yarn-package-manager-for-node-js

We must have at least Node 14 and Yarn 1.22

    wget -qO- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash
    sudo apt install nvm

Close and reopen the session so nvm is available:

    nvm install 14
    apt install npm
    npm install -g yarn

Then run:

    cd /var/www/plateforme-ebs
    yarn install
    yarn build


## Files

Be careful that is you run the commands as root some files may not have the correct
rights.
The following directory should be owned by the web server user:

    chown -R /var/www/plateforme-ebs/public 
    chown -R /var/www/plateforme-ebs/var 


## Parameters 

You can create a `.env.local` file containing:

    APP_ENV=prod
    APP_DEBUG=0
    APP_SECRET=xxxxxxx
    MEILISEARCH_URL=https://ms-xxxxxxxx-3263.lon.meilisearch.io
    MEILISEARCH_API_KEY=xxxxxxx
    PAYUM_APIKEY=test_FRxxxxxx
    REDIS_URL=redis://localhost:6379
    DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=14&charset=utf8"
    MAILER_DSN=smtp://mailer:1025
    SMS_DSN=null://null

Change the Meilisearch parameters you received before.
With this setup, mails are sent with a local SMTP server and SMS are disabled.

Then you can run the Meilisearch index command:

    sudo bin/console app:index-products --env=prod

That's it, access the root URL of your server, you should see the home page.
If you have a 500 error, you can change `APP_DEBUG` to 1 to see the errors.

More information about the parameters in this [file](./configuration.md).


## Conclusion

This is a working basic installation process.
Of course, it it better to use a deployment tool like [EasyDeploy](https://github.com/EasyCorp/easy-deploy-bundle), 
[Capistrano](https://github.com/capistrano/capistrano) or other ones.
