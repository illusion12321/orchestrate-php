Orchestrate.io PHP Client
======

This client goal is to follow very closely the Orchestrate API and naming conventions, so your best friend is always the great Orchestrate API Reference https://orchestrate.io/docs/apiref

You can follow the methods and usage example below, and feel free to fork and suggest improvements. Our goal is to build a very user-friendly client.

Important info:
- Uses [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- PHP should be 5.4 or higher.
- JSON is parsed as, and expected to be, associative array.


## Instalation

Use [Composer](http://getcomposer.org):

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of the client:

```bash
composer require andrefelipe/orchestrate-php
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```


## Instantiation
```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
// if you don't provide any parameters it will 
// get the environment variable 'ORCHESTRATE_API_KEY'
// and use the default host 'https://api.orchestrate.io' and API version 'v0;

// you can also provide the values, in order: key, host, version
$application = new Application($apiKey, 'https://api.aws-eu-west-1.orchestrate.io/', 'v0');

// check the success with Ping
$application->ping(); // returns boolean
```

## Getting Started
We define our classes following the same convention as Orchestrate, so we have:

1. **Application** — which holds the Guzzle client, and provides a client-like API interface to Orchestrate (more below).
2. **Collection** — which holds a collection name and provides the same API, but with one level-deeper.
3. **Objects** — the actual `KeyValue` and `Search` objects, which provides a object-like API and the HTTP response status.

Example:

```php
use andrefelipe\Orchestrate\Application;

// Client-like approach
$application = new Application();
$object = $application->get('collection_name', 'key'); // returns a KeyValue object
$object = $application->put('collection_name', 'key', ['title' => 'My Title']);
$object = $application->delete('collection_name', 'key');
// you can name the var as 'client' to feel more like it

// Object-like approach
$application = new Application();
$collection = $application->collection('collection_name');
$object = $collection->get('key');
$object->put(['title' => 'My Title']);
$object->delete();
```

Please note that the result of all operations, in both approaches, are exact the same, they all return *Objects*. And the *Objects* holds the results as well as the response status.

Full-example:

```php
$application = new Application();
$object = $application->get('collection_name', 'key');

if ($object->isSuccess())
{
    print_r($object->getValue());
    // Array
    // (
    //     [title] => My Title
    // )

}
else
{
    // in case if was an error, it would return results like these

    echo $object->getStatus(); // items_not_found
    // — the Orchestrate Error code
    
    echo $item->getStatusCode();  // 404
    // — the HTTP response status code

    echo $item->getStatusMessage(); // The requested items could not be found.
    // — the status message, in case of error, the Orchestrate message is used intead of the default HTTP status texts
    
    print_r($item->getBody());
    // Array
    // (
    //     [message] => The requested items could not be found.
    //     [details] => Array
    //         (
    //             [items] => Array
    //                 (
    //                     [0] => Array
    //                         (
    //                             [collection] => collection_name
    //                             [key] => key
    //                         )
    //                 )
    //         )
    //     [code] => items_not_found
    // )
    // — the full body of the response, in this case the Orchestrate error

}

```

All objects implements ArrayAccess and ArrayIterator, so you can access the results direcly:

```php

// for KeyValue objects, the resulting value is acessed like:

$object = $application->get('collection_name', 'key');

if (count($object)) // 1 in this case
{
    echo $object['title']; // My Title
    
    foreach ($object as $key => $value)
    {
        echo $key; // title
        echo $value; // My Title
    }
}

// as intended you can change the value, then put back to Orchestrate

$object['file_url'] = 'http://myfile.jpg';
$object->put();

if ($object->isSuccess()) {
    echo $object->getRef(); // the new ref
    echo $item->getStatus();  // ok
    echo $item->getStatusCode();  // 200
}

```

To sum:
- All requests are actually triggered from the **Objects**.
- They prepare the request options and send to the **Application** HTTP client.
- Then the **Objects** store and process the HTTP response, according to each use case.



## Usage

### Key/Value Get
… will write soon



## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list