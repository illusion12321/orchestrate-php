Orchestrate PHP Client
======

PHP client for Orchestrate.io API. 

This client goal is to follow very closely the Orchestrate API and naming conventions, so your best friend is always their great API Reference https://orchestrate.io/docs/apiref

You can follow the methods and usage example below, and feel free to fork and suggest improvements. Our goal is to build a very user-friendly client.

Uses Guzzle 5 as HTTP client, PHP should be 5.4 or higher. JSON is parsed as, and expected to be, associative array.

**Instalation**

Use Composer:
{
require: {
andrefelipe/orchestrate-php: “*”
}
}

**Instantiation**
use andrefelipe\Orchestrate\Application;
$application = new Application();
// if you don’t provide any parameters it will look the environment variable ‘ORCHESTRATE_API_KEY’ and use the default host ‘https://api.orchestrate.io' and API version ‘v0;
// otherwise you can use
$application = new Application($apiKey, ‘https://api.aws-eu-west-1.orchestrate.io/', ‘v0’);

**Getting Started**
… falar do Application / Collection / Objects

**Usage**
… will write soon


**Useful Notes**

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- If applicable, remember you can use a composite key like “{deviceID}_{sensorID}_{timestamp}" for your KeyValye keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list