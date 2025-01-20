# Configuration

This page documents the settings that can be configured as environment varibles
for the production environment.
Defaults value are shown in the [.env](../.env) file.

## Application

| name         | default value | 
|--------------|---------------|
| APP_ENV      | prod          | 
| APP_DEBUG    | false         |
| APP_SECRET   | -             | 

SMS_DSN=null://nullThe env and debug should always keep these values in the prod environement.
The secret is a random string that can be changed at each deploy.


## Database

    DATABASE_URL: postgresql://${POSTGRES_USER:-app}:${POSTGRES_PASSWORD:-!ChangeMe!}@database:5432/${POSTGRES_DB:-app}?serverVersion=${POSTGRES_VERSION:-14}

There are 4 parameters that should be configured as environment variables :

| name              | default value  | 
|-------------------|----------------|
| POSTGRES_USER     | app            | 
| POSTGRES_PASSWORD | !ChangeMe!     |
| POSTGRES_DB       | app            | 
| POSTGRES_VERSION  | 14             | 

The `POSTGRES_VERSION` should not be changed. 

The default DSN to access the database when using the Docker setup is `database`.


## Emails

    MAILER_DSN=

Check out the [Symfony documentation](https://symfony.com/doc/current/mailer.html#using-built-in-transports).
There are a number of services that can be used.
For example, to use a standart SMTP server use:

    MAILER_DSN=smtp://mailer:1025

Where mailer is the DSN of your SMTP server and 1025 the port to use.

To use Gmail with a secret key use:

    MAILER_DSN=gmail://email@example.com:secretkey@default

Note that to avoid having SPAM issues, you should use a dedicated service like 
Twilio, Sendgrid, Mailingblue...

## SMS

    SMS_DSN=null://null

This is the main parameter to send DNS. If you leave `null://null`, no SMS will
be sent.
It may be useful when having issues with your SMS provider and wanting to disable it temporarily.

Below are the supported value templates to use depending on your SMS provider.

### Brevo

    SMS_DSN=brevo://API_KEY@default?sender=PHONE_NUMBER OR NAME

You might also need to authorise your server's IP Address in Brevo's settings (section Security/Authorized IPs). 

### Twilio

    SMS_DSN=twilio://AccountSID:AuthToken@default?from=PHONE_NUMBER

## Meilisearch

    MEILISEARCH_URL=http://meilisearch:7700
    MEILISEARCH_API_KEY=ms

A local meilisearch instance is used by default.
But, you can also use a [managed service](https://cloud.meilisearch.com), in this
case, the parameters should look like:

    MEILISEARCH_URL=https://ms-id-id.subdomain.meilisearch.io
    MEILISEARCH_API_KEY=f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6f6


## Payum/Mollie

    PAYUM_APIKEY=test_FRabcdefghijklmnopqrstuvwxyzab
    PAYUM_GATEWAY=mollie

Payum with the Mollie gateway is used.
For now the mollie Gateway must be used, but, as Payum is used, another gateway
like Stripe could be added with some modifications on the code (feel to create a
PR for this). The are [official Stripe Gateways for Mollie](https://github.com/Payum/Payum/blob/master/docs/supported-gateways.md#official).

To configure your Mollie account, access the [dashboard](https://my.mollie.com/dashboard/login?lang=en).
Create an account. 

Click on the ["Developer" page](https://my.mollie.com/dashboard/org_17065949/developers/api-keys)
and create a test key.
The test key has the format `test_xxxxxxxxxxxxx`, then put this value in the `PAYUM_APIKEY`
variable.
The test key can be used immediatly, but to have a production key you will have
to send documents to confirm the identity of your company and to be able to start
to receive payments on your bank account.

Note, that this step can take some time, and should start the procedure the sooner
possible.
Meanwhile, you will have access the "fake" payment page to similate payments without
having to enter an actual credit card.

Once your account is validated, replace the test API key by the live API key. 


## Files/S3 bucket

The goal here is to have persistent data in the production environment.

Locally, the local file storage is used.
For tests, a memory storage is used.
In the production environment a S3 compatible bucket is used.
It has been tested with a [min.io](https://min.io/) service, there is a docker setup
available in the docker compose files.
Here are the default settings when using the min.io container:

    STORAGE_BUCKET=images
    STORAGE_ENDPOINT=http://storage:9000
    STORAGE_REGION=us-east-1
    STORAGE_USE_PATH_STYLE_ENDPOINT=true
    STORAGE_KEY=app
    STORAGE_SECRET=!ChangeMe!

If you want to use a managed service, change the values of these parameters to
match those of your provider.
Note that when using the min.io with the dev environment is just to test the S3
configuration. 
There is config example in the [config/packages/flysystem.yaml](../config/packages/flysystem.yaml)

The bucket must be configured to be public and the default visibulity of each storage
entry should also be public.
