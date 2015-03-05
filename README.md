Orchestrate.io PHP Client
======

A very user-friendly PHP client for [Orchestrate.io](https://orchestrate.io) DBaaS.

- PHP's magic [get/setter](http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members), [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php) built in.
- To create an object model, just extend a KeyValue and define the public properties.
- Orchestrate's [error responses](https://orchestrate.io/docs/apiref#errors) are honored.
- Uses [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- PHP must be 5.4 or higher.
- Adheres to PHP-FIG [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/)

This client follows very closely [Orchestrate's](https://orchestrate.io) naming conventions, so you can confidently rely on the Orchestrate API Reference: https://orchestrate.io/docs/apiref

*This library is still at 0.x version, there is a [lot of ideas](https://github.com/andrefelipe/orchestrate-php/blob/master/TODO.md) to look at*.

[![Latest Stable Version](https://poser.pugx.org/andrefelipe/orchestrate-php/v/stable.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![License](https://poser.pugx.org/andrefelipe/orchestrate-php/license.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)


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

1- **Application** — which holds the credentials and HTTP client, and provides a client-like API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();

$item = $application->get('collection', 'key'); // returns a KeyValue object
$item = $application->put('collection', 'key', ['title' => 'My Title']);
$item = $application->delete('collection', 'key');
// you can name the $application var as '$client' to feel more like a client
```

2- **Collection** — which holds a collection name and provides the same client-like API, but with one level-deeper.

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Collection;

$application = new Application();

$collection = new Collection('collection');
$collection->setApplication($application); // link to the client

$item = $collection->get('key');
$item = $collection->put('key', ['title' => 'My Title']);
$item = $collection->delete('key');
```

3- **Objects** — the actual Orchestrate objects, which provides a object-like API, as well as the results, response status, and pagination methods. They split in two categories:

**Single Objects**, which provides methods to manage a single entity (get/put/delete/etc):
- `KeyValue`, core to Orchestrate and our client, handles key/ref/value;
- `Ref`, a KeyValue subclass, adds the tombstone and reftime properties;
- `Event`, provides a similar API as the KeyValue, for the Event object;
- `SearchResult`, a KeyValue subclass, adds the score and distance properties.

**List of Objects**, which provides the results and pagination methods: 
- `KeyValues`, used for KeyValue List query
- `Refs`, used for Refs List query
- `Graph`, used for Graph Get query
- `Events`, used for Event List query
- `Search`, used for Search query, with support for Geo and Aggregates

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Objects\KeyValue;

$application = new Application();

$item = new KeyValue('collection', 'key'); // no API calls yet
$item->setApplication($application); // link to the client

if ($item->get()) { // API call to get the current key

    // returns boolean of success
}

$item->get('20c14e8965d6cbb0'); // get a specific ref

// add some values
$item->name = 'Lorem Ipsum';
$item->role = ['member', 'user'];
$item->mergeValue(['role' => ['admin']]); // merge values

// put back
if ($item->put()) {
    
    echo $item->toJson(JSON_PRETTY_PRINT);
    // {
    //     "kind": "item",
    //     "path": {
    //         "collection": "collection",
    //         "key": "key",
    //         "ref": "20c14e8965d6cbb0"
    //     },
    //     "value": {
    //         "name": "Lorem Ipsum",
    //         "role": [
    //             "member",
    //             "user",
    //             "admin"
    //         ]
    //     }
    // }
}

// delete the current ref
$item->delete(); 

// delete the entire key and its history
$item->purge(); 

// etc
```

Choosing one approach over the other is a matter of your use case. For one-stop actions you'll find easier to work with the Application or Collection. But on a programatically import, for example, it will be nice to use the objects directly because you can store and manage the data, then later do the API calls.

**Remember**, the credentials and the HTTP client are only available at the `Application` object, so all objects must reference to it in order to work. You can do so via the `setApplication` method.




## Responses

**Objects holds the results as well as the response status.**

Example:

```php
use andrefelipe\Orchestrate\Application;
$application = new Application();

$item = $application->get('collection', 'key'); // returns a KeyValue object

if ($item->isSuccess()) {

    print_r($item->getValue());
    // Array
    // (
    //     [title] => My Title
    // )
    // - the Value

    print_r($item->toArray());
    // Array
    // (
    //     [kind] => item
    //     [path] => Array
    //         (
    //             [collection] => collection
    //             [key] => key
    //             [ref] => 3eb18d8d034a3530
    //         )
    //     [value] => Array
    //         (
    //             [title] => My Title
    //         )
    // )
    // - array representation of the object


    echo $item->getRequestId();
    // ec96acd0-ac7b-11e4-8cf6-22000a0d84a1
    // - Orchestrate request id, X-ORCHESTRATE-REQ-ID

    echo $item->getRequestDate();
    // Wed, 04 Feb 2015 14:41:37 GMT
    // - the HTTP Date header

    echo $item->getRequestUrl();
    // https://api.orchestrate.io/v0/collection/key
    // - the effective URL that resulted in this response

} else {
    // in case if was an error, it would return results like these:

    echo $item->getStatus();
    // items_not_found
    // — the Orchestrate Error code
    
    echo $item->getStatusCode();
    // 404
    // — the HTTP response status code

    echo $item->getStatusMessage();
    // The requested items could not be found.
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
    //                             [collection] => collection
    //                             [key] => key
    //                         )
    //                 )
    //         )
    //     [code] => items_not_found
    // )
    // — the full body of the response, in this case, the Orchestrate error

    $response = $item->getResponse();
    // - GuzzleHttp\Message\Response
}

```


## Data Access

All objects implements PHP's magic [get/setter](http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members), [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, using either Object or Array syntax.

Example:

```php
// Considering KeyValue with the value of {"title": "My Title"}

$item = $application->get('collection', 'key');

// Object syntax
echo $item->title;

// Array syntax
echo $item['title'];
echo $item['another_prop']; // returns null if not set, but never error  

// as intended you can change the Value, then put back to Orchestrate
$item->file_url = 'http://myfile.jpg';

if ($item->put())) {
    echo $item->getRef(); // cbb48f9464612f20 (the new ref)
    echo $item->getStatus();  // ok
    echo $item->getStatusCode();  // 200
}

// at any time, get the Value out if needed
$value = $item->getValue();

// toArray() returns an Array representation of the object
print_r($item->toArray());
// Array
// (
//     [kind] => item
//     [path] => Array
//         (
//             [collection] => collection
//             [key] => key
//             [ref] => cbb48f9464612f20
//         )
//     [value] => Array
//         (
//             [title] => My Title
//         )
// )

// to Json too
$item->toJson(JSON_PRETTY_PRINT); 

// anywhere
$item->myprop->likes->toJson();


// Of course, it gets interesting on List objects like Search

$results = $application->search('collection', 'title:"The Title*"');

// where you can iterate over the results directly!
foreach ($results as $item) {
    
    // get its values
    $item->getValue(); // the Value
    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query

    // if relation was created successfuly
    if ($item->putRelation('kind', 'toCollection', 'toKey')) {

        // take the opportunity to post an event too
        $values = [
            'type' => 'relation',
            'to' => 'toKey',
            'ref' => $item->getRef(),
        ];
        $application->postEvent('collection', $item->getKey(), 'log', $values);
    }
}
```






# Orchestrate API


### Application Ping:
> returns Boolean

```php
if ($application->ping()) {
    // good
}
```


### Collection Delete:
> returns Boolean

```php
if ($application->deleteCollection('collection')) {
    // good
}
```


### Key/Value Get
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->get('collection', 'key');

// Approach 2 - Collection
$item = $collection->get('key');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);

if ($item->get()) // returns boolean of operation success

// Example of getting the object info
$item->getKey(); // string
$item->getRef(); // string
$item->getValue(); // ObjectArray of the Value
$item->toArray(); // array representation of the object
$item->getBody(); // array of the unfiltered HTTP response body
```


### Key/Value Put (create/update by key)
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->put('collection', 'key', ['title' => 'New Title']);

// Approach 2 - Collection
$item = $collection->put('key', ['title' => 'New Title']);

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->title = 'New Title';
$item->put(); // puts the whole current Value, only with the title changed
$item->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Application
$item = $application->put('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Collection
$item = $collection->put('key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->put(['title' => 'New Title'], true); // uses the current object Ref
```


**Conditional Put If-None-Match**:

Stores the value for the key if no key/value already exists.

```php
// Approach 1 - Application
$item = $application->put('collection', 'key', ['title' => 'New Title'], false);

// Approach 2 - Collection
$item = $collection->put('key', ['title' => 'New Title'], false);

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->put(['title' => 'New Title'], false);
```


### Key/Value Patch (partial update - Operations)
> returns KeyValue object

Please refer to the [API Reference](https://orchestrate.io/docs/apiref#keyvalue-patch) for all details about the operations.

```php
// use the Patch operation builder
use andrefelipe\Orchestrate\Query\PatchBuilder;

$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

// Approach 1 - Application
$item = $application->patch('collection', 'key', $patch);

// Approach 2 - Collection
$item = $collection->patch('key', $patch);

// Approach 3 - Object 
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->patch($patch);

// Warning: when patching, the object Value (retrievable with $item->getValue())
// WILL NOT be updated! Orchestrate does not (yet) return the Value body in
// Patch operations, and mocking on our side will be very inconsistent
// and an extra GET would have to issued anyway.

// As a solution, you can fetch the resulting Value, using the
// third parameter 'reload' as:
$item->patch($patch, null, true);

// it will reload the data with $item->get($item->getRef());
// if the patch was successful
```

**Conditional Patch (Operations) If-Match**:

Updates the value for the key if the value for this header matches the current ref value.

```php
$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

// Approach 1 - Application
$item = $application->patch('collection', 'key', $patch, '20c14e8965d6cbb0');

// Approach 2 - Collection
$item = $collection->patch('key', $patch, '20c14e8965d6cbb0');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->patch($patch, '20c14e8965d6cbb0');
$item->patch($patch, true); // uses the current object Ref
$item->patch($patch, true, true); // with the reload as mentioned above
```


### Key/Value Patch (partial update - Merge)
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->patchMerge('collection', 'key', ['title' => 'New Title']);

// Approach 2 - Collection
$item = $collection->patchMerge('key', ['title' => 'New Title']);

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->title = 'New Title';
$item->patchMerge(); // merges the current Value
$item->patchMerge(['title' => 'New Title']); // or merge with new value
// also has a 'reload' parameter as mentioned above
```


**Conditional Patch (Merge) If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Application
$item = $application->patchMerge('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Collection
$item = $collection->patchMerge('key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->patchMerge(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->patchMerge(['title' => 'New Title'], true); // uses the current object Ref
// also has a 'reload' parameter as mentioned above
```



### Key/Value Post (create & generate key)
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->post('collection', ['title' => 'New Title']);

// Approach 2 - Collection
$item = $collection->post(['title' => 'New Title']);

// Approach 3 - Object
$item = new KeyValue('collection');
$item->setApplication($application);
$item->title = 'New Title';
$item->post(); // posts the current Value
$item->post(['title' => 'New Title']); // posts a new value
```


### Key/Value Delete
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->delete('collection', 'key');

// Approach 3 - Collection
$item = $collection->delete('key');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->delete();
$item->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
// Approach 1 - Application
$item = $application->delete('collection', 'key', '20c14e8965d6cbb0');

// Approach 2 - Collection
$item = $collection->delete('key', '20c14e8965d6cbb0');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
// first get or set a ref:
// $item->get();
// or $item->setRef('20c14e8965d6cbb0');
$item->delete(true); // delete the current ref
$item->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Purge**:

The KV object and all of its ref history will be permanently deleted. This operation cannot be undone.

```php
// Approach 1 - Application
$item = $application->purge('collection', 'key');

// Approach 2 - Collection
$item = $collection->purge('key');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->purge();
```



### Key/Value List:
> returns KeyValues object, with results as KeyValue objects

```php
// Approach 1 - Application
$list = $application->listCollection('collection');

// Approach 2 - Collection
$list = $collection->listCollection();

// Approach 3 - Object
$list = new KeyValues('collection'); // note the plural
$list->setApplication($application);
$list->listCollection();


// now get array of the results
$list->getResults();

// or go ahead and iterate over the results directly!
foreach ($list as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results
```



### Refs Get:
> returns KeyValue object

Returns the specified version of a value.

```php
// Approach 1 - Application
$item = $application->get('collection', 'key', '20c14e8965d6cbb0');

// Approach 2 - Collection
$item = $collection->get('key', '20c14e8965d6cbb0');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->get('20c14e8965d6cbb0');
```

### Refs List:
> returns Refs object, with results as Ref objects (a KeyValue subclass)

Get the specified version of a value.

```php
// Approach 1 - Application
$list = $application->listRefs('collection', 'key');

// Approach 2 - Collection
$list = $collection->listRefs('key');

// Approach 3 - Object
$list = new Refs('collection', 'key');
$list->setApplication($application);
$list->listRefs();


// now get array of the results
$list->getResults();

// or go ahead and iterate over the results directly!
foreach ($list as $item) {
    
    echo $item->title;
    // items are Ref objects (KeyValue subclass)
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results
```



### Search:
> returns Search object, with results as SearchResult objects (a KeyValue subclass)

```php
// Approach 1 - Application
$results = $application->search('collection', 'title:"The Title*"');

// Approach 2 - Collection
$results = $collection->search('title:"The Title*"');

// Approach 3 - Object
$results = new Search('collection');
$results->setApplication($application);
$results->search('title:"The Title*"');


// now get array of the search results
$list_of_items = $results->getResults();

// or go ahead and iterate over the results directly!
foreach ($results as $item) {
    
    echo $item->title;
    // items are SearchResult objects, KeyValue subclass

    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query
}

// aggregates
$results->getAggregates(); // array of the Aggregate results, if any 

// pagination
$results->getNextUrl(); // string
$results->getPrevUrl(); // string
$results->getCount(); // count of the current set of results
$results->getTotalCount(); // count of the total results available
$results->next(); // loads next set of results
$results->prev(); // loads previous set of results
```

All Search parameters are supported, and it includes [Geo](https://orchestrate.io/docs/apiref#geo-queries) and [Aggregates](https://orchestrate.io/docs/apiref#aggregates) queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
// public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)

// aggregates example
$results = $collection->search(
    'value.created_date:[2014-01-01 TO 2014-12-31]',
    null,
    'value.created_date:time_series:month'
);
```





### Event Get
> returns Event object

```php
// Approach 1 - Application
$event = $application->getEvent('collection', 'key', 'type', 1400684480732, 1);

// Approach 2 - Collection
$event = $collection->getEvent('key', 'type', 1400684480732, 1);

// Approach 3 - Object
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->setApplication($application);
$event->get();
```

### Event Put (update)
> returns Event object

```php
// Approach 1 - Application
$event = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title']);

// Approach 2 - Collection
$event = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title']);

// Approach 3 - Object
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->setApplication($application);
$event->title = 'New Title';
$event->put(); // puts the whole current value, only with the title changed
$event->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Application
$event = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Collection
$event = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 3 - Object
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->setApplication($application);
$event->title = 'New Title';
$event->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$event->put(['title' => 'New Title'], true); // uses the current object Ref
```


### Event Post (create)
> returns Event object

```php
// Approach 1 - Application
$event = $application->postEvent('collection', 'key', 'type', ['title' => 'New Title']);

// Approach 2 - Collection
$event = $collection->postEvent('key', 'type', ['title' => 'New Title']);

// Approach 3 - Object
$event = new Event('collection', 'key', 'type');
$event->setApplication($application);
$event->title = 'New Title';
$event->post(); // posts the current Value
$event->post(['title' => 'New Title']); // posts a new value
$event->post(['title' => 'New Title'], 1400684480732); // optional timestamp
$event->post(['title' => 'New Title'], true); // use stored timestamp
```


### Event Delete
> returns Event object

Warning: Orchestrate do not support full history of each event, so the delete operation have the purge=true parameter.

```php
// Approach 1 - Application
$event = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1);

// Approach 2 - Collection
$event = $collection->deleteEvent('key', 'type', 1400684480732, 1);

// Approach 3 - Object
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->setApplication($application);
$event->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
// Approach 1 - Application
$event = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');

// Approach 2 - Collection
$event = $collection->deleteEvent('key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');

// Approach 3 - Object
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->setApplication($application);
// first get or set a ref:
$event->get();
// or $event->setRef('20c14e8965d6cbb0');
$event->delete(true); // delete the current ref
$event->delete('20c14e8965d6cbb0'); // delete a specific ref
```


### Event List:
> returns Events object, with results as Event objects

```php
// Approach 1 - Application
$events = $application->listEvents('collection', 'key', 'type');

// Approach 2 - Collection
$events = $collection->listEvents('key', 'type');

// Approach 3 - Object
$events = new Events('collection', 'key', 'type'); // note the plural
$events->setApplication($application);
$events->listEvents();


// now get array of the results
$events->getResults();

// or go ahead and iterate over the results directly!
foreach ($events as $event) {
    
    echo $event->title;
    // items are Event objects
}

// pagination
$events->getNextUrl(); // string
$events->getPrevUrl(); // string
$events->getCount(); // count of the current set of results
$events->getTotalCount(); // count of the total results available
$events->next(); // loads next set of results
$events->prev(); // loads previous set of results
```









### Graph Get (List):
> returns Graph object, with results as KeyValue objects

Returns relation's collection, key, ref, and values. The "kind" parameter(s) indicate which relations to walk and the depth to walk. Relations aren't fetched by unit, so the result will always be a List.

```php
// Approach 1 - Application
$list = $application->listRelations('collection', 'key', 'kind');

// Approach 2 - Collection
$list = $collection->listRelations('key', 'kind');

// Approach 3 - Object
$list = new Graph('collection', 'key', 'kind');
$list->setApplication($application);
$list->listRelations();


// the kind parameter accepts an array of strings to request the relatioship depth:
$list = $application->listRelations('collection', 'key', ['kind', 'kind2']);
// two hops


// get array of the results (KeyValue objects)
$list->getResults();

// or go ahead and iterate over the results directly
foreach ($list as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results

```


### Graph Put
> returns KeyValue object

```php
// Approach 1 - Application
$item = $application->putRelation('collection', 'key', 'kind', 'toCollection', 'toKey');

// Approach 2 - Collection
$item = $collection->putRelation('key', 'kind', 'toCollection', 'toKey');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->putRelation('kind', 'toCollection', 'toKey');
```


### Graph Delete
> returns KeyValue object

Deletes a relationship between two objects. Relations don't have a history, so the operation have the purge=true parameter.

```php
// Approach 1 - Application
$item = $application->deleteRelation('collection', 'key', 'kind', 'toCollection', 'toKey');

// Approach 2 - Collection
$item = $collection->deleteRelation('key', 'kind', 'toCollection', 'toKey');

// Approach 3 - Object
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
$item->deleteRelation('kind', 'toCollection', 'toKey');
```





## Docs

Please refer to the source code for now, while a proper documentation is made.

Here is a sample of the KeyValue Class methods: 


## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- When adding a field for a date, suffix it with '_date' or other [supported prefixes](https://orchestrate.io/docs/apiref#sorting-by-date);
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list;

