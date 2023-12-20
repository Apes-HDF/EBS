# Composer

This file documents the content of the `composer.json` for custom stuff.


## webbaard/payum-mollie

For the Mollie gatework we use a fork of:

    https://github.com/webbaard/payum-mollie

Which is itself a fork of:

    https://github.com/PayHelper/payum-mollie

This last payum Mollie gateway is not up to date.
That's why we use the fork that supports the last Mollie 2.x API and SDK.

We did a copy of the fork to avoid being tied to a specific vendor.
The fork is in the `coopTilleuls` vendor, this organization is behind the  
[API Platform](https://github.com/api-platform/) open source project and can be
trusted.

As the library is about payments, it is important to mitigate [supply chain attacks](https://dunglas.dev/2023/05/mitigate-attacks-on-your-php-supply-chain/). 

As soon as the the offical PayHelper Mollie gate will be updated, we will switch
to the official gateway and archive this temporary fork.
A [PR](https://github.com/PayHelper/payum-mollie/pull/8) has been open.


## easycorp/easyadmin-bundle

The version is fixed to `v4.5.1`, to avoid a problem with enumerations. 
The last time I tried, the issue was still there. 
Check if it can be fixed more easily with the `4.6` version.
