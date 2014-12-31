Orchestrate.io PHP Client
======

This client follows very closely the Orchestrate API and naming conventions, so your best friend is always the (great) Orchestrate API Reference: https://orchestrate.io/docs/apiref

- Uses [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- PHP should be 5.4 or higher.
- JSON is parsed as, and expected to be, associative array.
- You may find it a very user-friendly client.


## Instalation

Use [Composer](http://getcomposer.org).

Install Composer Globally (Linux / Unix / OSX):

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Run this Composer command to install the latest stable version of the client, in the current folder:

```bash
composer require andrefelipe/orchestrate-php
```

After installing, require Composer's autoloader and you're good to go:

```php
<?php
require 'vendor/autoload.php';
```


## Instantiation
```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
// if you don't provide any parameters it will:
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

// you can also provide the parameters, in order: apiKey, host, version
$application = new Application(
    'your-api-key',
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

// check the success with Ping
$application->ping(); // (boolean)
```

## Getting Started
We define our classes following the same convention as Orchestrate, so we have:

1- **Application** — which holds the Guzzle client, and provides a client-like API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$object = $application->get('collection_name', 'key'); // returns a KeyValue object
$object = $application->put('collection_name', 'key', ['title' => 'My Title']);
$object = $application->delete('collection_name', 'key');
// you can name the var as '$client' to feel more like a client
```

2- **Collection** — which holds a collection name and provides the same client-like API, but with one level-deeper.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$collection = $application->collection('collection_name');
$object = $collection->get('key');
$object = $collection->put('key', ['title' => 'My Title']);
$object = $collection->delete('key');
```

3- **Objects** — the actual `KeyValue` and `Search` objects, which provides a object-like API, as well as the results and response status.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$object = new KeyValue($application, 'collection_name', 'key'); // no API calls yet
// you can now change the object as you like, then do the requests later
$object->get(); // the current stored key
$object->get('20c14e8965d6cbb0'); // a specific ref
$object->put(['title' => 'My Title']); // puts a new value
$object->delete(); // delete the current ref
```

Please note that the result of all operations, in any approach, are exact the same, they all return **Objects**. And ***Objects* holds the results as well as the response status.**

Example:

```php
$application = new Application();
$object = $application->get('collection_name', 'key');

if ($object->isSuccess()) {
    print_r($object->getValue());
    // Array
    // (
    //     [title] => My Title
    // )
} else {
    // in case if was an error, it would return results like these:

    echo $object->getStatus(); // items_not_found
    // — the Orchestrate Error code
    
    echo $item->getStatusCode();  // 404
    // — the HTTP response status code

    echo $item->getStatusMessage(); // The requested items could not be found.
    // — the status message, in case of error, the Orchestrate message is used
    // intead of the default HTTP Reason-Phrases
    
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
    // — the full body of the response, in this case, the Orchestrate error

}

```

All objects implements PHP's [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, like a real Array:

```php

// for KeyValue objects, the Value is acessed like:

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

// as intended you can change the Value, then put back to Orchestrate
$object['file_url'] = 'http://myfile.jpg';
$object->put();

if ($object->isSuccess()) {
    echo $object->getRef(); // cbb48f9464612f20 (the new ref)
    echo $object->getStatus();  // ok
    echo $object->getStatusCode();  // 200
}

// if you don't want to use the internal Array directly, you can always use:
$value = $object->getValue();
// it will return the internal Array that is being accessed
// then you can change it as usual
$value['profile'] = ['name' => 'The Name', 'age' => 10];
// and send to Orchestrate with:
$object->put($value);
// or with:
$object = $application->put('collection_name', $object->getKey(), $value);

if ($object->isSuccess()) {}

```

To sum, this is how this client works:
- All requests are actually triggered from the **Objects**.
- They prepare the request options and send to the **Application** HTTP client.
- Then the **Objects** store the HTTP response and process the results, according to each use case.

Let's go:


## Orchestrate API

### Key/Value Get

```php
$object = $application->get('collection', 'key');
// or
$object = $collection->get('key');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->get();
```

### Key/Value Put (create/update by key)

```php
$object = $application->put('collection', 'key', ['title' => 'New Title']);
// or
$object = $collection->get('key', ['title' => 'New Title']);
// or
$object = new KeyValue($application, 'collection', 'key');
$object['title'] = 'New Title';
$object->put(); // puts the whole current value, only with the title changed
$object->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$object = $application->put('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = $collection->get('key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$object->put(['title' => 'New Title'], true); // uses the current object Ref
```


**Conditional Put If-None-Match**:

Stores the value for the key if no key/value already exists.

```php
$object = $application->put('collection', 'key', ['title' => 'New Title'], false);
// or
$object = $collection->get('key', ['title' => 'New Title'], false);
// or
$object = new KeyValue($application, 'collection', 'key');
$object->put(['title' => 'New Title'], false);
```


### Key/Value Post (create & generate key)

```php
$object = $application->post('collection', ['title' => 'New Title']);
// or
$object = $collection->post(['title' => 'New Title']);
// or
$object = new KeyValue($application, 'collection');
$object['title'] = 'New Title';
$object->post(); // posts the current Value, if it has changed
$object->post(['title' => 'New Title']); // posts a new value
```


### Key/Value Delete

```php
$object = $application->delete('collection', 'key');
// or
$object = $collection->delete('key');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->delete();
$object->delete('20c14e8965d6cbb0'); // delete the specific ref
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$object = $application->delete('collection', 'key', '20c14e8965d6cbb0');
// or
$object = $collection->delete('key', '20c14e8965d6cbb0');
// or
$object = new KeyValue($application, 'collection', 'key');
// first get or set a ref:
// $object->get();
// or $object->setRef('20c14e8965d6cbb0');
$object->delete(true); // delete the current ref
$object->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Purge**:

The KV object and all of its ref history will be permanently deleted. This operation cannot be undone.

```php
$object = $application->purge('collection', 'key');
// or
$object = $collection->purge('key');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->purge();
```



### Refs Get:

Returns the specified version of a value.

```php
$object = $application->get('collection', 'key', '20c14e8965d6cbb0');
// or
$object = $collection->get('key', '20c14e8965d6cbb0');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->get('20c14e8965d6cbb0');
```



### Search Collection:

```php
$object = $application->search('collection', 'title:"The Title*"');
// or
$object = $collection->search('title:"The Title*"');
// or
$object = new Search($application, 'collection');
$object->search('title:"The Title*"');
```

All Search parameters are supported, and it includes Geo queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search)
```php
search($query, $sort='', $limit=10, $offset=0)
```


## Docs

Please refer to the source code for now, while a proper documentation is made.

Here is a sample of the KeyValue Class methods: 

### Key/Value
```php
$object = $application->get('collection', 'key');

if ($object->isSuccess()) {
    
    // get the object info
    $object->getKey(); // string
    $object->getRef(); // string
    $object->getValue(); // array
    
    // working with the Value
    $object['my_property']; // direct array access to the Value
    foreach ($object as $key => $value) {} // iteratable
    $object['my_property'] = 'new value'; // set
    unset($object['my_property']); // unset
    
    // some API methods
    $object->put(); // put the current value, if has changed, otherwise return
    $object->put(null); // same as above
    $object->put(['title' => 'new title']); // put a new value
    $object->delete(); // delete the current ref
    $object->delete('20c14e8965d6cbb0'); // delete the specific ref
    $object->purge(); // permanently delete all refs and graph relations

    // booleans to check status
    $object->hasChanged(); // if the internal Array has changed
    $object->isSuccess(); // if the last request was sucessful
    $object->isError(); // if the last request was not sucessful
    
    $object->getResponse(); // GuzzleHttp\Message\Response
    $object->getStatus(); // ok, created, items_not_found, etc
    $object->getStatusCode(); // (int) the HTTP response status code
    $object->getStatusMessage(); // Orchestrate response message, or HTTP Reason-Phrase

    $object->getRequestId(); // Orchestrate request id, X-ORCHESTRATE-REQ-ID
    $object->getRequestDate(); // the HTTP Date header
    $object->getRequestUrl(); // the effective URL that resulted in this response
    
    $object->getBody(); // array of the HTTP response body
    // if success is the same as $object->getValue()
    // if error you can read the response error body

}
```

Here is a sample of the Search Class methods: 

### Search
```php
$object = $application->search('collection', 'title:"The Title*"');

if ($object->isSuccess()) {
    
    // get the object info
    $object->getBody(); // array of the full HTTP response body
    $object->getResults(); // array of only the 'results' index

    // pagination
    $object->getNextUrl(); // string
    $object->getPrevUrl(); // string
    $object->getCount(); // available to match the syntax, but is exactly the same as count($object)
    $object->getTotalCount();
    
    // working with the Results
    $object[0]; // direct array access to the Results
    foreach ($object as $item) {} // iterate thought the Results
    count($object); // the Results count

    // booleans to check status
    $object->isSuccess(); // if the last request was sucessful
    $object->isError(); // if the last request was not sucessful
    
    $object->getResponse(); // GuzzleHttp\Message\Response
    $object->getStatus(); // ok, created, items_not_found, etc
    $object->getStatusCode(); // (int) the HTTP response status code
    $object->getStatusMessage(); // Orchestrate response message, or HTTP Reason-Phrase

    $object->getRequestId(); // Orchestrate request id, X-ORCHESTRATE-REQ-ID
    $object->getRequestDate(); // the HTTP Date header
    $object->getRequestUrl(); // the effective URL that resulted in this response

}
```



## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list
