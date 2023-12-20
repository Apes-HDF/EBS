# Symfony 

We tried to respect Symfony best practices.
Everything is not perfect of course, there is still some cleanup to do but globally
the project is clean.

* PHPStan is at the maximum level without ignore annotations
* Code coverage is at 100% (some mock are used)
* php-cs-fixer is used

DTO are used almost everywhere and we tried to avoid using arrays.
This makes the code more robust and easer to read. It also enable more autocompletion
with the code editors.


## Global architecture

A simplified version of CQRS is used.
It it required when doing more complex processing
to have a better decoupling between controllers and services.
For more simple actions, like editing the user profile, standard forms are used.
CQRS stuff can be found in:

* Message
* MessageBus
* MessageHandler

For now all messages are synchronous but everything is ready to pass some messages
to asynchronous if there are too heavy processes.


## Doctrine

We used the make bundle to generate entities.
Fixtures are loaded thanks to the Alice bundle.
These fixtures are used fo tests.
We tried to use Doctrine in the most standard way.
There are some behaviors used (timestamble, nested set) and UUID are used for all
table main identifiers. 
Doctrine migrations are initialized.


## EasyAdmin

We tried to get the maximum out of EasyAdmin without adding to much custom code.

* There is custom code to be able to use Flysystem
* There is JavaScript code for files uploads

The most important thing is the part to securize the access for group administrators
because they only need to access their data.


## Api Platform

API Platform is used for some methods in the user connected space.
It's more to showcase the use of API Platform 3 along with Symfony 6, UX and Stimulus.

 
## Tests

PHPUnit is used and the code coverage is 100%. These tests are grouped by type:

* API tests
* End to end tests
* Functional tests
* Integration tests
* Unit tests

The structure in each of this directory reflects what we can find in the `src/` folder.

The coverage can by generated with:

    make coverage

Some tests need to be cleaned the whole test suite is stable.
Mocks are used for the Geocoder calls because 500 errors where raised the GitHub CI. 
So it is to avoid this.
Translations are deactivated in the test env so we can use key code instead of real
texts, this makes the tests less fragile.


## Workflow

The workflow component is used the manage the state of the loans which is at the
heart of the application.
The loan state workflow can be found in [database.md](database.md). 
The workflow Twig helpers are used in the templates to keep the templates clean.
