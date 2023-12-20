# Errors

This documention gives solutions for some errors occuring in the project.

## 1. Makefile

### Error 1.1

    plateformcoop-ebs$ docker compose exec php ./vendor/bin/php-cs-fixer fix --allow-risky=yes
    OCI runtime exec failed: exec failed: unable to start container process: exec: "./vendor/bin/php-cs-fixer": permission denied: unknown
    
### Solution 1.1

Verify that the executables in `vendor/bin`have the executblale right:

    cd /srv/app/vendor/bin; chmod +x *


## 2. PHPStan 

### Error 2.1

    tests/Integration/Translator/NotranslatorTest.php                          
    --------------------------------------------------------------------------- 
    Service "App\Translator\NoTranslator" is not registered in the container.
    ---------------------------------------------------------------------------

### Solution 2.1

To resolve this error, run:
    
    make stan-cc


## 3. PHPUnit

### Error 3.1

    ..........................................make: *** [Makefile:159: test] Error 137
    
### Solution 3.1

Check if there is no `dump()` in a loop.

### Error 3.2

    ..................make: *** [test] Error 139
    
### Solution 3.2

It seems to be a temporary Docker error. Run again the tests. Try the debug mode
`make test-debug` if the error is still there to find the faulty tests.


### Error 3.3 

```
1) App\Tests\Functional\Controller\User\ServiceRequest\ServiceRequestStatusWorkflowControllerRefuseTest::testTransitionRefuseSuccess

LogicException: The selected node does not have a form ancestor.

/srv/app/vendor/symfony/dom-crawler/Form.php:372
/srv/app/vendor/symfony/dom-crawler/AbstractUriElement.php:45
/srv/app/vendor/symfony/dom-crawler/Form.php:38
/srv/app/vendor/symfony/dom-crawler/Crawler.php:838
/srv/app/tests/Functional/Controller/User/ServiceRequest/ServiceRequestStatusWorkflowControllerRefuseTest.php:35
```

It's because we have the same traduction in two places.

```
<div class="d-grid col mt-2">
    <button type="button" class="btn btn-danger"
            data-bs-toggle="modal"
            data-bs-target="#refuseModal">
        <i class="bi bi-x"></i>
        {{ (i18n_prefix ~ '.link.refuse')|trans }} <== HERE
    </button>
</div>


<button type="submit"
        class="btn btn-danger">
    {{ (i18n_prefix ~ '.link.refuse')|trans }} <== AND HERE
</button>
```

### Solution 3.2

```
<div class="d-grid col mt-2">
    <button type="button" class="btn btn-danger"
            data-bs-toggle="modal"
            data-bs-target="#refuseModal">
        <i class="bi bi-x"></i>
        {{ (i18n_prefix ~ '.link.refuse_modal')|trans }} <== CHANGE FIRST KEY TRADE
    </button>
</div>
```

Change the traduction key for the first button


## 4. Geocoding/Nominatim

### Error 4.1

Error for all the geocoding tests (poc or modify my address):

    ErrorException: Handling "App\Message\Query\Admin\User\UserAddressQuery" failed: Serialization of 'Closure' is not allowed 
    in /srv/app/vendor/symfony/messenger/Middleware/HandleMessageMiddleware.php:129

It is because the Nominatim service is down (error 502, bad gateway).
Disable the cache in `config/packages/bazinga_geocoder.yaml` to see the real error:

    The geocoder server returned an invalid response (502) for query "https://nominatim.openstreetmap.org/search?format=jsonv2&q=Timipi%2C%20Fives%2C%20france&addressdetails=1&extratags=1&limit=3&accept-language=fr".
    We could not parse it.

    https://nominatim.openstreetmap.org/search?format=jsonv2&q=Timipi%2C%20Fives%2C%20france&addressdetails=1&extratags=1&limit=3&accept-language=fr"
    
    502 Bad Gateway
    nginx

### Solution 4.1

Wait.

It would be nice if the bundle could hande this error correclty and at least return
an empty results array if there is such an error.
As it is made now, we can't catch this error, which is quite problematic.
Create a new issue on the bundle to see what can be done.

### Todo for 4.1

Use mocks for the test env. 


## 5. Docker

### Error 5.1

When running a command with make like `make cs` we have he error:

    OCI runtime exec failed: exec failed: unable to start container process: read init-p: connection reset by peer: unknown

### Solution 5.1

Restart Docker.


## 6. Symfony

### Error 6.1

When trying to access a controller we just created:

   Could not resolve argument $productId of "App\Controller\User\Product\DeleteProductAction::__invoke()", maybe you forgot to register the controller as a service or missed tagging it with the "controller.service_arguments"?

### Solution 6.1

There is a mismatch between the arguments of the controller action and the route
requirements, fix them (eg: `productId` instead of `id`.



## 7. Meilisearch

### Error 7.1

When indexing a document:

    The primary key inference failed as the engine found 2 fields ending with `id` in their names: 'id' and 'ownerId'.
    Please specify the primary key manually using the `primaryKey` query parameter?
   
### Solution 6.1

The primary key must specified to avoid confusion. It can be set using the second
argument of the `addDocument()` functions.

    $this->getIndex()->addDocuments([$this->normalizeProduct($product)], self::PRIMARY_KEY);
