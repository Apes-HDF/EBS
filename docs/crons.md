# CRON

The following CRON must be installed:

## Handle membership end:

* remove the group association from the user
* remove the products from the group and change their visibility
* notify the user by email and SMS


    bin/console app:end-membership --env=prod


## Notifiy users for membership expiration

* the day before the membership is over:


    bin/console app:notify-membership-expiration 1 --env=prod

* A week before the membership is over:
  

    bin/console app:notify-membership-expiration 7 --env=prod

As you can see, can can run and pass the number of days you want. 
If you want to notify one month before the end of annual membershipt, the query
should be modified to exclude monthly membership.


## Notify users for services requests start and end

Notify owner and recipient when a service requests starts tomorrow:

    bin/console app:notify-service-request-dates start --env=prod

Notify owner and recipient when a service request end tomorrow:

    bin/console app:notify-service-request-dates end --env=prod


## Help

All commands are documented. You can get it by using the help command, eg:

    bin/console help app:notify-membership-expiration

```shell
Description:
Notify expiring membership.

Usage:
app:notify-membership-expiration <days>

Arguments:
days                  Number of days from tomorrow (1 = notifiy members expiring tomorrow)

Options:
-h, --help            Display help for the given command. When no command is given display help for the list command
-q, --quiet           Do not output any message
-V, --version         Display this application version
--ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
-n, --no-interaction  Do not ask any interactive question
-e, --env=ENV         The Environment name. [default: "dev"]
--no-debug        Switch off debug mode.
-v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
Notify expiring membership.

COMMAND:
App\Command\NotifyMembershipExpirationCommand

DEV:
bin/console app:notify-membership-expiration -vv

PROD:
bin/console app:notify-membership-expiration --env=prod --no-debug
```
