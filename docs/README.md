# Plateforme EBS

« Simplicity is the ultimate sophistication » - Leonardo da Vinci

## Prerequisites (at least)

* docker v20.10+ (`docker --version`)

## Initializing and starting the project with Docker

Clone the project:

    git clone git@github.com:ApesHDF/EBS.git

Check that your 80 and 443 ports are free, then build and start the Docker containers:

    make build
    make start

Access `https://localhost` in your browser and accept the security risk.

You should have access now to:

* Main project : https://localhost
* Meilisearch : http://localhost:7700/

To access the dev tools, run=

    make start-dev

You should have access now to:

* Adminer : http://localhost:8989/?pgsql=database&username=app&db=app&ns=public&select=group
* Maildev : http://localhost:1080

## Makefile

Check the [Makefile](Makefile) file to see all available commands.
In this project, the commands must be called outside the container.
If you want to use the Makefile from within the PHP container, just add `-n`
to the make call, eg:

    make stan -n

Which outputs:

    docker compose exec php ./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 1G    

Enter the PHP container:

    make sh

Then run the wanted command without the docker part (`docker compose exec`):

    php ./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 1G

It's generally more conveniant to run Symfony commands inside the container:

    bin/console debug:container

## Development

Create the dev database and load fixtures:

    make load-fixtures

Create the test database and load fixtures:

    make load-test-fixtures

Run the tests and generate the code coverage report:

    make coverage

Run all checks like the Github CI:

    make ci

### Import a commit into customers repo

1. go to customer repo tree
2. add les tilleuls repo as a remote : `git remote add source git@github.com:coopTilleuls/plateformcoop-ebs.git`
3. change to sync branch : `git checkout chore/sync-les-tilleuls`
4. update the branch by merging main : `git merge main`
5. update source remote : `git fetch source`
6. cherry-pick the commits you want : `git cherry-pick <commit ref>`
