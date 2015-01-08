Orchestrate.io PHP Client
======

This client follows very closely the Orchestrate API and naming conventions, so your best friend is always the Orchestrate API Reference: https://orchestrate.io/docs/apiref

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

1- **Application** — which holds the HTTP client, and provides a client-like API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();

$object = $application->get('collection', 'key'); // returns a KeyValue object
$object = $application->put('collection', 'key', ['title' => 'My Title']);
$object = $application->delete('collection', 'key');
// you can name the $application var as '$client' to feel more like a client
```

2- **Collection** — which holds a collection name and provides the same client-like API, but with one level-deeper.

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Collection;

$application = new Application();

$collection = new Collection('collection');

$object = $collection->get('key');
$object = $collection->put('key', ['title' => 'My Title']);
$object = $collection->delete('key');
```

3- **Objects** — the actual Orchestrate objects, which provides a object-like API, as well as the results, response status, and pagination methods. They split in two categories:

**Single Objects**, which provides methods to manage a single entity (get/put/delete/etc):
- `KeyValue`, core to Orchestrate and our client, handles key/ref/value;
- `Ref`, a KeyValue subclass, adds tombstone and reftime properties;
- `Event`, provides a similar API as the KeyValue, for the Event object;
- `SearchResult`, a KeyValue subclass, adds score and distance properties.

**List of Objects**, which provides the results and methods for pagination: 
- `KeyValues`, used for KeyValue List query, with KeyValue instances as result
- `Refs`, used for Refs List query, with Ref instances as result
- `Graph`, used for Graph Get query, with KeyValue instances as result
- `Events`, used for Event List query, with Event instances as result
- `Search`, used for Search query, with SearchResult instances as result

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Objects\KeyValue;

$application = new Application();

$object = new KeyValue('collection', 'key'); // no API calls yet
$object->get(); // API call to get the current key
$object->get('20c14e8965d6cbb0'); // get a specific ref
$object->put(['title' => 'My Title']); // puts a new value
$object->delete(); // delete the current ref
```

Please note that the result of all operations, in any approach, are exact the same, they all return **Objects**. And ***Objects* holds the results as well as the response status.**

Example:

```php
$application = new Application();
$object = $application->get('collection', 'key');

if ($object->isSuccess()) {

    print_r($object->getValue());
    // Array
    // (
    //     [title] => My Title
    // )

    print_r($object->toArray());
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

} else {
    // in case if was an error, it would return results like these:

    echo $object->getStatus(); // items_not_found
    // — the Orchestrate Error code
    
    echo $object->getStatusCode();  // 404
    // — the HTTP response status code

    echo $object->getStatusMessage(); // The requested items could not be found.
    // — the status message, in case of error, the Orchestrate message is used
    // intead of the default HTTP Reason-Phrases
    
    print_r($object->getBody());
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

}

```

All objects implements PHP's [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, like a real Array:

```php
$object = $application->get('collection', 'key');

$object->getValue(); // array of the Value
$object['my_property']; // direct array access to the Value
foreach ($object as $key => $value) {}  // iterate thought the Value
count($object); // the Value count
$object['my_property'] = 'new value'; // set
unset($object['my_property']); // unset


$object = $application->search('collection', 'title:"The Title*"');

$object->getResults(); // array of SearchResult objects
$object[0]; // direct array access to the Results
foreach ($object as $item) {} // iterate thought the Results
count($object); // the Results count
$object['my_property'] = 'new value'; // set, throws Exception
unset($object['my_property']); // unset, throws Exception
// there is no point on changing the search result array,
// but you can of course manage each item:
foreach ($object as $item) {
    $item->putRelation('kind', 'toCollection', 'toKey');
    if ($item->isSuccess()) {
        // do something else
    }
}
```

Example:

```php

// for KeyValue objects, the Value can be accessed like:

$object = $application->get('collection', 'key');

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
$object = $application->put('collection', $object->getKey(), $value);

if ($object->isSuccess()) {
    // good
}


// also all objects provide an additional method, toArray
// which returns an Array representation of the object
print_r($object->toArray());
// Array
// (
//     [kind] => item
//     [path] => Array
//         (
//             [collection] => collection
//             [key] => key
//             [ref] => cbb48f9464612f20
//             [reftime] => 1400085084739
//             [score] => 1.0
//             [tombstone] => true
//         )
//     [value] => Array
//         (
//             [title] => My Title
//         )
// )


```

**Final note:**

The HTTP client is only available at the `Application` object, so all objects must reference to it in order to work. You can do so via:
```php
$object = new KeyValue('collection', 'key');
$object->setApplication($application);
// where $application is an Application instance

```
But that will be rarelly necessary as, when the application is not set, the objects will automatically try to reference to the last created instance of `Application`, which can be check at:
```php
Application::getCurrent();
```

The current application is automatically set every time we create a `new Application` instance, but you can change it via:
```php
Application::setCurrent($application);
```

This behaviour only affects when creating new `Objects` instances directly. When using the client API (via application or collection instances), they **will always refer to the parent Application**.


Let's go:



## Orchestrate API


### Application Ping:
> returns Boolean

```php
if ( $application->ping() ) {
    // good
}
```


### Collection Delete:
> returns Collection object

```php
$object = $application->deleteCollection('collection');
// or
$collection = new Collection('collection');
$collection->deleteCollection();
```


### Key/Value Get
> returns KeyValue object

```php
$object = $application->get('collection', 'key');
// or
$object = $collection->get('key');
// or
$object = new KeyValue('collection', 'key');
$object->get();

// get the object info
$object->getKey(); // string
$object->getRef(); // string
$object->getValue(); // array of the Value
$object->toArray(); // array representation of the object
$object->getBody(); // array of the unfiltered HTTP response body
```

### Key/Value Put (create/update by key)
> returns KeyValue object

```php
$object = $application->put('collection', 'key', ['title' => 'New Title']);
// or
$object = $collection->put('key', ['title' => 'New Title']);
// or
$object = new KeyValue('collection', 'key');
$object['title'] = 'New Title';
$object->put(); // puts the whole current Value, only with the title changed
$object->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$object = $application->put('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = $collection->get('key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = new KeyValue('collection', 'key');
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
$object = new KeyValue('collection', 'key');
$object->put(['title' => 'New Title'], false);
```


### Key/Value Post (create & generate key)
> returns KeyValue object

```php
$object = $application->post('collection', ['title' => 'New Title']);
// or
$object = $collection->post(['title' => 'New Title']);
// or
$object = new KeyValue('collection');
$object['title'] = 'New Title';
$object->post(); // posts the current Value
$object->post(['title' => 'New Title']); // posts a new value
```


### Key/Value Delete
> returns KeyValue object

```php
$object = $application->delete('collection', 'key');
// or
$object = $collection->delete('key');
// or
$object = new KeyValue('collection', 'key');
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
$object = new KeyValue('collection', 'key');
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
$object = new KeyValue('collection', 'key');
$object->purge();
```



### Key/Value List:
> returns KeyValues object

```php
$object = $application->listCollection('collection');
// or
$collection = new Collection('collection');
$object = $collection->listCollection();
// or
$object = new KeyValues('collection'); // note the plural
$object->listCollection();


// get array of the results (KeyValue objects)
$object->getResults();

// or go ahead and iterate over the results directly
foreach ($object as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$object->getNextUrl(); // string
$object->getPrevUrl(); // string
$object->getCount(); // count of the current set of results
$object->getTotalCount(); // count of the total results available
$object->next(); // loads next set of results
$object->prev(); // loads previous set of results
```



### Refs Get:
> returns KeyValue object

Returns the specified version of a value.

```php
$object = $application->get('collection', 'key', '20c14e8965d6cbb0');
// or
$object = $collection->get('key', '20c14e8965d6cbb0');
// or
$object = new KeyValue('collection', 'key');
$object->get('20c14e8965d6cbb0');
```

### Refs List:
> returns Refs object

Get the specified version of a value.

```php
$object = $application->listRefs('collection', 'key');
// or
$object = $collection->listRefs('key');
// or
$object = new Refs('collection', 'key');
$object->listRefs();


// get array of the results (Ref objects)
$object->getResults();

// or go ahead and iterate over the results directly
foreach ($object as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$object->getNextUrl(); // string
$object->getPrevUrl(); // string
$object->getCount(); // count of the current set of results
$object->getTotalCount(); // count of the total results available
$object->next(); // loads next set of results
$object->prev(); // loads previous set of results
```



### Search:
> returns Search object, with results as SearchResult objects (a KeyValue subclass)

```php
$object = $application->search('collection', 'title:"The Title*"');
// or
$object = $collection->search('title:"The Title*"');
// or
$object = new Search('collection');
$object->search('title:"The Title*"');


// get array of the search results (SearchResult objects)
$object->getResults();

// or go ahead and iterate over the results directly
foreach ($object as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$object->getNextUrl(); // string
$object->getPrevUrl(); // string
$object->getCount(); // count of the current set of results
$object->getTotalCount(); // count of the total results available
$object->next(); // loads next set of results
$object->prev(); // loads previous set of results
```

All Search parameters are supported, and it includes Geo queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)
```





### Event Get
> returns Event object

```php
$object = $application->getEvent('collection', 'key', 'type', 1400684480732, 1);
// or
$object = $collection->getEvent('key', 'type', 1400684480732, 1);
// or
$object = new Event('collection', 'key', 'type', 1400684480732, 1);
$object->get();
```

### Event Put (update)
> returns Event object

```php
$object = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title']);
// or
$object = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title']);
// or
$object = new Event('collection', 'key', 'type', 1400684480732, 1);
$object['title'] = 'New Title';
$object->put(); // puts the whole current value, only with the title changed
$object->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$object = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$object = new Event('collection', 'key', 'type', 1400684480732, 1);
$object['title'] = 'New Title';
$object->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$object->put(['title' => 'New Title'], true); // uses the current object Ref
```


### Event Post (create)
> returns Event object

```php
$object = $application->postEvent('collection', 'key', 'type', ['title' => 'New Title']);
// or
$object = $collection->postEvent('key', 'type', ['title' => 'New Title']);
// or
$object = new Event('collection', 'key', 'type');
$object['title'] = 'New Title';
$object->post(); // posts the current Value
$object->post(['title' => 'New Title']); // posts a new value
$object->post(['title' => 'New Title'], 1400684480732); // optional timestamp
$object->post(['title' => 'New Title'], true); // use stored timestamp
```


### Event Delete
> returns Event object

Warning: Orchestrate do not support full history of each event, so the delete operation have the purge=true parameter.

```php
$object = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1);
// or
$object = $collection->deleteEvent('key', 'type', 1400684480732, 1);
// or
$object = new Event('collection', 'key', 'type', 1400684480732, 1);
$object->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$object = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');
// or
$object = $collection->deleteEvent('key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');
// or
$object = new Event('collection', 'key', 'type', 1400684480732, 1);
// first get or set a ref:
// $object->get();
// or $object->setRef('20c14e8965d6cbb0');
$object->delete(true); // delete the current ref
$object->delete('20c14e8965d6cbb0'); // delete a specific ref
```


### Event List:
> returns Events object

```php
$object = $application->listEvents('collection', 'key', 'type');
// or
$collection = new Collection('collection');
$object = $collection->listEvents('key', 'type');
// or
$object = new Events('collection', 'key', 'type'); // note the plural
$object->listEvents();


// get array of the results (Event objects)
$object->getResults();

// or go ahead and iterate over the results directly
foreach ($object as $item) {
    
    $item->getValue();
    // items are Event objects
}

// pagination
$object->getNextUrl(); // string
$object->getPrevUrl(); // string
$object->getCount(); // count of the current set of results
$object->getTotalCount(); // count of the total results available
$object->next(); // loads next set of results
$object->prev(); // loads previous set of results
```









### Graph Get (List):
> returns Graph object

Returns relation's collection, key, ref, and values. The "kind" parameter(s) indicate which relations to walk and the depth to walk. Relations aren't fetched by unit, so the result will always be a List.

```php
$object = $application->listRelations('collection', 'key', 'kind');
// or
$collection = new Collection('collection');
$object = $collection->listRelations('key', 'kind');
// or
$object = new Graph('collection', 'key', 'kind');
$object->listRelations();


// the kind parameter accepts an array of strings to request the relatioship depth:
$object = $application->listRelations('collection', 'key', ['kind', 'kind2']);
// two hops


// get array of the results (KeyValue objects)
$object->getResults();

// or go ahead and iterate over the results directly
foreach ($object as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$object->getNextUrl(); // string
$object->getPrevUrl(); // string
$object->getCount(); // count of the current set of results
$object->getTotalCount(); // count of the total results available
$object->next(); // loads next set of results
$object->prev(); // loads previous set of results

```


### Graph Put
> returns KeyValue object

```php
$object = $application->putRelation('collection', 'key', 'kind', 'toCollection', 'toKey');
// or
$object = $collection->putRelation('key', 'kind', 'toCollection', 'toKey');
// or
$object = new KeyValue('collection', 'key');
$object->putRelation('kind', 'toCollection', 'toKey');
```


### Graph Delete
> returns KeyValue object

Deletes a relationship between two objects. Relations don't have a history, so the operation have the purge=true parameter.

```php
$object = $application->deleteRelation('collection', 'key', 'kind', 'toCollection', 'toKey');
// or
$object = $collection->deleteRelation('key', 'kind', 'toCollection', 'toKey');
// or
$object = new KeyValue('collection', 'key');
$object->deleteRelation('kind', 'toCollection', 'toKey');
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
    $object->toArray(); // array representation of the object
    $object->getBody(); // array of the HTTP response body
    
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

Here is a sample of the Search Class methods: 

### Search
```php
$object = $application->search('collection', 'title:"The Title*"');

if ($object->isSuccess()) {
    
    // get the object info
    $object->getResults(); // array of the search results
    $object->toArray(); // array representation of the object
    $object->getBody(); // array of the full HTTP response body

    // pagination
    $object->getNextUrl(); // string
    $object->getPrevUrl(); // string
    $object->getCount(); // available to match the syntax, but is exactly the same as count($object)
    $object->getTotalCount();
    $object->next(); // loads next set of results
    $object->prev(); // loads previous set of results, if available
    
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
