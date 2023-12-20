# Geoloc / GIS

* https://symfony-devs.slack.com/archives/C3F1WBW5R/p1665562230485859

## Symfony bundle

* https://github.com/geocoder-php/BazingaGeocoderBundle/blob/master/doc/index.md

This bundle handles multiples providers.
The nominatim provider is free and can be chained with paying provider in case it
doesn't work or the free service isn't stable enough for the project needs.

## OpenStreetmap (Nominatim) Provider

* https://wiki.openstreetmap.org/wiki/Nominatim
* https://github.com/geocoder-php/nominatim-provider

This provider is free and is a good starting point.

Be careful that it has restrictions and the consummer must respect 
the [policy](https://operations.osmfoundation.org/policies/nominatim/).


## Cache

A cache is implemented, so when a user does the same query, the result cache is
used instead of querying the geocoding service another time for the same query.


## CLI Tests

Getting the information for a given address:

    bin/console geocoder:geocode "82 Rue Winston Churchill, 59160, Lomme, FRANCE"

Clearing the specific geoloc cache:

    bin/console cache:pool:clear cache.geoloc
