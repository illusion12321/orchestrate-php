Orchestrate.io PHP Client
======

A very user-friendly PHP client for [Orchestrate.io](https://orchestrate.io) DBaaS.

- Choose which approach you prefer, [client-like or object-like](#getting-started).
- PHP's magic [get/setter](http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members), [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php) built in.
- [Template engine friendly](#objectarray), with [JMESPath](#jmespath) support.
- Create [Models](#models) by extending our classes, and easily change child class.
- [toArray/toJson](#data-access) methods produces the same output format as Orchestrate's export.
- Orchestrate's [error responses](https://orchestrate.io/docs/apiref#errors) are honored.
- Adheres to PHP-FIG [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/)

Requirements:
- PHP must be 5.4 or higher.
- [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- [JMESPath](https://github.com/jmespath/jmespath.php).

This client follows very closely [Orchestrate's](https://orchestrate.io) naming conventions, so you can confidently rely on the Orchestrate API Reference: https://orchestrate.io/docs/apiref

*This library is still at 0.x version, there is a [lot of ideas](https://github.com/andrefelipe/orchestrate-php/blob/master/TODO.md) to look at*.

[![Latest Stable Version](https://img.shields.io/packagist/v/andrefelipe/orchestrate-php.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![Total Downloads](https://img.shields.io/packagist/dt/andrefelipe/orchestrate-php.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/andrefelipe/orchestrate-php/master/LICENSE.md)

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




## Getting Started
You can use our library in two distinct ways:

### 1- Client
##### A straightforward API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Client;

// provide the parameters, in order: apiKey, host, version
$client = new Client(
    'your-api-key',
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

$client = new Client();
// if you don't provide any parameters it will:
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

// check the connection success with Ping (returns boolean)
if ($client->ping()) {
    // OK
}

// use it
$item = $client->get('collection', 'key'); // returns a KeyValue object
$item = $client->put('collection', 'key', ['title' => 'My Title']);
$item = $client->delete('collection', 'key');

// To check the success of an operation use:
if ($item->isSuccess()) {
    // OK, API call sucessful

    // (more on using the results and responses below)
}

// IMPORTANT: The result of all operations by the Client are Objects (see next).
```

### 2- Objects
##### Actual Orchestrate objects (Collection, KeyValue, Event, etc), which provide an object API as well as the response status.

```php
use andrefelipe\Orchestrate\Application;

$app = new Application();
// same credentials constructor as the Client above applies
// they both share the same client capabilities

$collection = $client->collection('collection');
$item = $collection->item('key');
// no API calls where made yet

if ($item->get()) { // API call to get the current key

    // IMPORTANT: The result of all operations in Objects are boolean!

    // let's add some values
    $item->name = 'Lorem Ipsum';
    $item->role = ['member', 'user', 'admin'];

    // put back
    if ($item->put()) {
        
        print_r($this->getValue());
        // andrefelipe\Orchestrate\Common\ObjectArray Object
        // (
        //     [name] => Lorem Ipsum
        //     [role] => andrefelipe\Orchestrate\Common\ObjectArray Object
        //         (
        //             [0] => member
        //             [1] => user
        //             [2] => admin
        //         )
        // )
        // ObjectArray is a dynamic class that allows object or array syntax access
        // i.e. $item->name or $item['name'], plus a few other helpful methods.
        // This means you can feel free to send it to your template engine.
        // More on that next.

        echo $item->toJson(JSON_PRETTY_PRINT);
        // {
        //     "kind": "item",
        //     "path": {
        //         "collection": "collection",
        //         "kind": "item",
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
        // Same output format as Orchestrate's export

        // take the opportunity to create a relation to another item
        $anotherItem = $collection->item('another-key');

        if ($item->relation('kind', $anotherItem)->put()) {

            // if the relation was successful
            // take the opportunity to post an event too
            $values = [
                'type' => 'relation',
                'to_item' => $anotherItem->getKey(),
                'current_ref' => $item->getRef(),
            ];
            $item->event('log')->post($values);
        }
    }

    // delete the current ref
    $item->delete(); 

    // delete the entire key and its history
    $item->purge(); 

    // etc
}

```

Choosing one approach over the other is a matter of your use case. For one-stop actions you'll find easier to work with the Client. But on a programatically import, for example, it will be nice to use the objects directly because you can store and manage the data, then later do the API calls.

**Note**, the credentials and the HTTP client are only available at the `Application` or `Client` objects, so all objects must reference to them in order to do API class. You can do so via the `setClient` method. But that's only needed if you want to instantiate the objects directly. If you use the methods as described above, that's handled for you.



## Responses

**Important: Objects holds the results as well as the response status.**

Example:

```php
$item = $collection->item('key'); // returns a KeyValue object

if ($item->get()) {

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

$item = $collection->item('key');

if ($item->get()) {

    // Object syntax
    echo $item->title;

    // Array syntax
    echo $item['title'];
    echo $item['another_prop']; // returns null if not set, but never error!

    // as intended you can change the Value, then put back to Orchestrate
    $item->file_url = 'http://myfile.jpg';

    if ($item->put())) {
        echo $item->getRef(); // cbb48f9464612f20 (the new ref)
        echo $item->getStatus();  // ok
        echo $item->getStatusCode();  // 200
    }

    // at any time, get the Value out if needed
    print_r($item->getValue());
    // andrefelipe\Orchestrate\Common\ObjectArray Object
    // (
    //     [title] => My Title
    //     [file_url] => http://myfile.jpg
    // )

    // toArray() returns an Array representation of the object
    print_r($item->toArray());
    // Array
    // (
    //     [kind] => item
    //     [path] => Array
    //         (
    //             [collection] => collection
    //             [kind] => item
    //             [key] => key
    //             [ref] => cbb48f9464612f20
    //         )
    //     [value] => Array
    //         (
    //             [title] => My Title
    //             [file_url] => http://myfile.jpg
    //         )
    // )

    // to Json too
    $item->toJson(JSON_PRETTY_PRINT);
    // {
    //     "kind": "item",
    //     "path": {
    //         "collection": "collection",
    //         "kind": "item",
    //         "key": "key",
    //         "ref": "cbb48f9464612f20"
    //     },
    //     "value": {
    //         "title": "My Title",
    //         "file_url": "http://myfile.jpg"
    //     }
    // }

    // any property that is an array inherits the same functionality
    $item->myprop = ['likes' => []];
    $item->myprop->likes[] = 'computers';
    $item->myprop->likes[] = 'code';
    $item->myprop->likes->toJson(); // ["computers","code"]
    $item->myprop->toJson(); // {"likes":["computers","code"]}
}



// it also gets interesting on a collection of items

if ($collection->search('collection', 'title:"The Title*"')) {

    // where you can iterate over the results directly!
    foreach ($collection as $item) {
        echo $item->title;
    }
}

```





## ObjectArray

ObjectArray turns a simple Array into an object, which can be accessed via object or array syntax plus a few other handy methods like toArray, toJson, merge and extract.

Why is that important? Because it makes your data more accessible to you, and to your template engine.




## Template Engine

Easily send your data to your favorite template engine. Also find handy methods to quickly format the data output.

For example:
```php
// Phalcon / Volt - http://phalconphp.com

// gets 50 members, sorted by created_date
$members->search('*', 'created_date:desc', null, 50);

// in a controller you can send the entire object to the view:
$this->view->members = $members;

// then in volt:
<ul class="members-list">
    {%- for member in members %}
        <li><a href="/members/{{ member.getKey() }}/">{{ member.name }}</a></li>
    {%- endfor %}
</ul>

// you may be interested anyway in sending ONLY the data to the view,
// without exposing the KeyValue objects and their API methods (get/put..)

// so send each member's Value only
$this->view->members = $members->getValues();

// or format the data using a JMESPath expression
$this->view->members = $members->extract('results[].{name: value.fullname, country: value.country, slug: path.key}');

// if you don't need the 'path' use extractValues method for a less verbose expression
$this->view->members = $members->extractValues('[].{name: fullname, country: country}');


// Same for single items
$item = $members->item('key');
$item->get();

$this->view->member = $item;
$this->view->member = $item->getValue();
$this->view->member = $item->extractValue('{name: fullname, country: country}');

// then in volt:
<p>Member {{ member.name }}, {{ member.country }}</p>



// Same approach works for any template engine
```



## JMESPath

```php
// 'extract' method uses the toArray() as data source
$result = $collection->extract('[].{name: value.name, thumb: value.thumbs[0], slug: path.key)}');
$result = $item->extract('{name: value.name, thumb: value.thumbs[0]}');

// 'extractValue' uses getValue() as data source, so you can use less verbose expressions
// with the drawback that you can't access the 'path' data (key/ref/etc...)
$result = $collection->extractValues('[].{name: name, thumb: thumbs[0])}');
$result = $item->extractValue('{name: name, thumb: thumbs[0]}');
```

**NOTE** When a JMESPath result is an array, it will be automatically wrapped into an ObjectArray.





## Models

There's one major decision on our library: **KeyValue and Event's values are stored in the object itself**. So when you get a property with $item->myProp you are accessing it directly. 

That won't differ much from an Array memory-wise, because we are storing data as dynamic vars. But by extending a KeyValue class and defining our public properties you can actually reduce the memory allocation.

```php
// Member.php
namespace MyProject\Models;

use \andrefelipe\Orchestrate\Objects\KeyValue;

class Member extends KeyValue
{
    public $name;

    public $role = 'guest';
    // feel free to set the defaults!
    
    public $birth_date;
    // unset or null vars do not get Put into Orchestrate.
    // Rest assure that setting the public vars here will not be stored
    // in your account, if they are null.
    // To check what is going to be stored there, use the toArray() method


    // getter/setters are fine too!
    // you could use it you favor to add validation, error protection, custom defaults, etc

    protected $country_code;

    protected $created_date;
    // protected and private vars get out of the public scope
    // and therefore do not compose the Value data

    public function __construct()
    {
        $this->mapProperty('created_date');
        $this->mapProperty('country_code');

        // mapProperty will automatically try to match the methods named
        // after your property with camelCase, for example 'getName'.

        // you can also strictly name the get/setters to map to:
        // $this->mapProperty('birth_date', 'getBirth', 'setBirth');
    }

    public function getCreatedDate()
    {
        // custom default example:
        if (!isset($this->created_date)) {
            $this->created_date = date(DATE_W3C);
        }
        return $this->created_date;
    }

    public function setCreatedDate($value)
    {
        $this->created_date = $value;
    }
    
    public function getCountryCode()
    {
        // custom default example:
        if (!isset($this->country_code)) {
            $this->country_code = 'us';
        }
        return $this->country_code;
    }

    public function setCountryCode($value)
    {
        $this->country_code = $value;
        // here you could add a check if the country code is valid
        // could match to a country name and store it too, etc
    }
}
```

Two distinct things happen when using custom getter/setters:

1- Access of the defined property gets mapped to the respective method. For example $item->country_code actually calls getCountryCode and $item->country_code = 'us' actually calls setCountryCode.

2- You strictly notify which additional properties the object should handle, so it knows what to include in the Value that will be sent to Orchestrate.

>> Remember, the Value data is composed of all your public properties plus the custom one defined with mapProperty. To check what will be sent to Orchestrate use the toArray method.

The example above is a class that will represent each item in a Collection.

In that case you will also want to create a class to act as your Collection.


```php
// Members.php
namespace MyProject\Models;

use \andrefelipe\Orchestrate\Application;
use \andrefelipe\Orchestrate\Objects\Collection;

class Members extends Collection
{
    public function __construct(Application $client)
    {
        // set client
        $this->setClient($client);

        // set collection
        $this->setCollection('members');
        $this->setChildClass('\MyProject\Models\Member');
    }
}

// at this approach, whenever we create items from this collection,
// it will be Member objects
$members = new \MyProject\Models\Members($app);

$item = $members->item('john'); // instance of '\MyProject\Models\Member'

```

If you are using the Client approach, you can change which classes to use with: 
```php

$client->setKeyValueClass($class);
$client->setEventClass($class);

// where $class is a fully qualified name of a class that implements, at minimum:
// \andrefelipe\Orchestrate\Objects\KeyValueInterface for KeyValue
// \andrefelipe\Orchestrate\Objects\EventInterface for Event
```










# Orchestrate API


### Application Ping:

```php
// Approach 1 - Client
if ($client->ping()) {
    // success
}

// Approach 2 - Object
if ($client->ping()) {
    // success
}
```


### Collection Delete:

```php
// Approach 1 - Client
if ($client->deleteCollection('collection')) {
    // success
}

// Approach 2 - Object
// To prevent accidental deletions, provide the current collection name as
// the parameter. The collection will only be deleted if both names match.
if ($collection->delete('collection')) {
    // success
}

// Warning this will permanently erase all data
// within the collection and cannot be reversed!
```


### Key/Value Get

```php
// Approach 1 - Client
$item = $client->get('collection', 'key'); // returns KeyValue object

// check operation success with:
if ($item->isSuccess()) {
    // ok, request was successful
}

// Approach 2 - Object
$item = $collection->item('key');

// you can check operation success direcly
if ($item->get()) {
    // returns boolean of operation success
}

// Example of getting the object info
$item->getKey(); // string
$item->getRef(); // string
$item->getValue(); // ObjectArray of the Value
$item->toArray(); // Array representation of the object
$item->toJson(); // Json representation of the object
$item->getBody(); // Array of the unfiltered HTTP response body

```


### Key/Value Put (create/update by key)

```php
// Approach 1 - Client
$item = $client->put('collection', 'key', ['title' => 'New Title']);

// Approach 2 - Object
$item = $collection->item('key'); // no API calls yet
$item->put(['title' => 'New Title']); // puts a new value
// or manage the value then put later
$item->title = 'New Title';
$item->put();
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Client
$item = $client->put('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$item->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->put(['title' => 'New Title'], true); // uses the current object Ref
```


**Conditional Put If-None-Match**:

Stores the value for the key if no key/value already exists.

```php
// Approach 1 - Client
$item = $client->put('collection', 'key', ['title' => 'New Title'], false);

// Approach 2 - Object
$item = $collection->item('key');
$item->put(['title' => 'New Title'], false);
```


### Key/Value Patch (partial update - Operations)

Please refer to the [API Reference](https://orchestrate.io/docs/apiref#keyvalue-patch) for all details about the operations.

```php
// use the Patch operation builder
use andrefelipe\Orchestrate\Query\PatchBuilder;

$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

// Approach 1 - Client
$item = $client->patch('collection', 'key', $patch);

// Approach 2 - Object 
$item = $collection->item('key');
$item->patch($patch);

// Warning: when patching, the object Value (retrievable with $item->getValue())
// WILL NOT be updated! Orchestrate does not (yet) return the Value body in
// Patch operations, and mocking on our side will be very inconsistent
// and an extra GET would have to issued anyway.

// As a solution, you can fetch the resulting Value, using the
// third parameter 'reload' as:
$item->patch($patch, null, true);
// it will reload the data if the patch was successful
```

**Conditional Patch (Operations) If-Match**:

Updates the value for the key if the value for this header matches the current ref value.

```php
$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

// Approach 1 - Client
$item = $client->patch('collection', 'key', $patch, '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$item->patch($patch, '20c14e8965d6cbb0');
$item->patch($patch, true); // uses the current object Ref
$item->patch($patch, true, true); // with the reload as mentioned above
```


### Key/Value Patch (partial update - Merge)

```php
// Approach 1 - Client
$item = $client->patchMerge('collection', 'key', ['title' => 'New Title']);

// Approach 2 - Object
$item = $collection->item('key');
$item->title = 'New Title';
$item->patchMerge(); // merges the current Value
$item->patchMerge(['title' => 'New Title']); // or merge with new value
// also has a 'reload' parameter as mentioned above
```


**Conditional Patch (Merge) If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Client
$item = $client->patchMerge('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$item->patchMerge(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->patchMerge(['title' => 'New Title'], true); // uses the current object Ref
// also has a 'reload' parameter as mentioned above
```



### Key/Value Post (create & generate key)

```php
// Approach 1 - Client
$item = $client->post('collection', ['title' => 'New Title']);

// Approach 2 - Object
$item = $collection->item('key');
$item->post(['title' => 'New Title']); // posts a new value
// or manage the object values then post later
$item->title = 'New Title';
$item->post();
```


### Key/Value Delete

```php
// Approach 1 - Client
$item = $client->delete('collection', 'key');

// Approach 2 - Object
$item = $collection->item('key');
$item->delete();
$item->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
// Approach 1 - Client
$item = $client->delete('collection', 'key', '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
// first get the item, or set a ref:
// $item->get();
// or $item->setRef('20c14e8965d6cbb0');
$item->delete(true); // delete the current ref
$item->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Purge**:

The KV object and all of its ref history will be permanently deleted. This operation cannot be undone.

```php
// Approach 1 - Client
$item = $client->purge('collection', 'key');

// Approach 2 - Object
$item = $collection->item('key');
$item->purge();
```



### Key/Value List:

```php
// range parameter is optional, but when needed
// use the Key Range operation builder
use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

$range = (new KeyRangeBuilder())
    ->from('blue')
    ->to('zinc');

$range = (new KeyRangeBuilder())
    ->from('blue', false) // key 'blue' is excluded, if exists
    ->to('zinc', false); // key 'zinc' is excluded, if exists

// you can also use the between method
$range->between('blue', 'zinc');

// in either method, keys can also be a KeyValue object
$range->from($item)->to($anotherItem);


// Approach 1 - Client
$collection = $client->listCollection('collection', 100, $range);

// Approach 2 - Object
$collection->get(100, $range);

// Please note, the max limit currently imposed by Orchestrate is 100


// now get array of the results
$collection->getResults();

// or go ahead and iterate over the results directly!
foreach ($collection as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$collection->getNextUrl(); // string
$collection->getPrevUrl(); // string
count($collection); // count of the current set of results
$collection->getTotalCount(); // count of the total results, if available
$collection->nextPage(); // loads next set of results
$collection->prevPage(); // loads previous set of results
```



### Refs Get:

Returns the specified version of a value.

```php
// Approach 1 - Client
$item = $client->get('collection', 'key', '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$item->get('20c14e8965d6cbb0');
```

### Refs List:

Get the specified version of a value.

```php
// Approach 1 - Client
$refs = $client->listRefs('collection', 'key');

// Approach 2 - Object
$item = $collection->item('key');
$refs = $item->refs();
$refs->get(100);

// now get array of the results
$refs->getResults();

// or go ahead and iterate over the results directly!
foreach ($refs as $item) {
    
    echo $item->title;
}

// pagination
$refs->getNextUrl(); // string
$refs->getPrevUrl(); // string
count($refs); // count of the current set of results
$refs->getTotalCount(); // count of the total results available
$refs->nextPage(); // loads next set of results
$refs->prevPage(); // loads previous set of results
```



### Search:

```php
// Approach 1 - Client
$collection = $client->search('collection', 'title:"The Title*"');

// Approach 2 - Object
$collection->search('title:"The Title*"');


// now get array of the search results
$itemList = $results->getResults();

// or go ahead and iterate over the results directly!
foreach ($collection as $item) {
    
    echo $item->title;

    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query
}

// aggregates
$collection->getAggregates(); // array of the Aggregate results, if any 

// pagination
$collection->getNextUrl(); // string
$collection->getPrevUrl(); // string
count($collection); // count of the current set of results
$collection->getTotalCount(); // count of the total results available
$collection->nextPage(); // loads next set of results
$collection->prevPage(); // loads previous set of results
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

```php
// Approach 1 - Client
$event = $client->getEvent('collection', 'key', 'type', 1400684480732, 1);

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->get();
```

### Event Put (update)

```php
// Approach 1 - Client
$event = $client->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title']);

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->put(['title' => 'New Title']); // puts a new value
// or manage the value then put later
$event->title = 'New Title';
$event->put();
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
// Approach 1 - Client
$event = $client->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$event->put(['title' => 'New Title'], true); // uses the current object Ref, in case you have or loaded before with ->get()
```


### Event Post (create)

```php
// Approach 1 - Client
$event = $client->postEvent('collection', 'key', 'type', ['title' => 'New Title']);

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type');

if ($event->post(['title' => 'New Title'])) {
    // success

    // you can also chain the methods if you like:
    // $item->event('type')->post(['title' => 'New Title'])
}

$event->post(); // posts the current Value
$event->post(['title' => 'New Title']); // posts a new value
$event->post(['title' => 'New Title'], 1400684480732); // optional timestamp
$event->post(['title' => 'New Title'], true); // use stored timestamp
```


### Event Delete

Warning: Orchestrate do not support full history of each event, so the delete operation have the purge=true parameter.

```php
// Approach 1 - Client
$event = $client->deleteEvent('collection', 'key', 'type', 1400684480732, 1);

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
// Approach 1 - Client
$event = $client->deleteEvent('collection', 'key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');

// Approach 2 - Object
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->delete(true); // delete the current ref
$event->delete('20c14e8965d6cbb0'); // delete a specific ref
```


### Event List:

```php
// range parameter is optional, but when needed
// use the Time Range operation builder
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

$range = (new TimeRangeBuilder())
    ->from('1994-11-06T01:49:37-07:00')
    ->to('2015-11-06T01:49:37-07:00');
// use any supported timestamp format as described here:
// https://orchestrate.io/docs/apiref#events-timestamps

$range = (new TimeRangeBuilder())
    ->from(784111777000, false) // excludes events that match the start time, if exists
    ->to(784111777221, false); // excludes events that match the end time, if exists

// if you don't need millisecond precision, confortably use the 'Date' methods
$range = (new TimeRangeBuilder())
    ->fromDate('yesterday')
    ->toDate('now'));
// any of the following formats are accepted:
// (1) A valid format that strtotime understands;
// (2) A integer, that will be considered as seconds since epoch;
// (3) A DateTime object; 

// you can also use the between method
$range->betweenDate('2015-03-09', '2015-03-11');

// keys can also be an Event object
$range->from($event)->to($anotherEvent);


// Approach 1 - Client
$events = $client->listEvents('collection', 'key', 'type', 10, $range);

// Approach 2 - Object
$item = $collection->item('key');
$events = $item->events('type'); // note the plural 'events'
$events->get(10, $range);


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
count($events); // count of the current set of results
$events->getTotalCount(); // count of the total results available
$events->nextPage(); // loads next set of results
$events->prevPage(); // loads previous set of results
```









### Graph Get (List):

Returns relation's collection, key, ref, and values. The "kind" parameter(s) indicate which relations to walk and the depth to walk. Relations aren't fetched by unit, so the result will always be a List.

```php
// Approach 1 - Client
$relations = $client->listRelations('collection', 'key', 'kind');

// Approach 2 - Object
$item = $collection->item('key');
$relations = $item->relations('kind');
$relations->get(100);

// Kind param can be array too, to indicate the depth to walk

// get array of the results (KeyValue objects)
$relations->getResults();

// or go ahead and iterate over the results directly
foreach ($relations as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$relations->getNextUrl(); // string
$relations->getPrevUrl(); // string
count($relations); // count of the current set of results
$relations->getTotalCount(); // count of the total results available
$relations->nextPage(); // loads next set of results
$relations->prevPage(); // loads previous set of results

```


### Graph Put

```php
// Approach 1 - Client
$item = $client->putRelation('collection', 'key', 'kind', 'toCollection', 'toKey');

// Approach 2 - Object
$item = $collection->item('key');
$anotherItem = $collection->item('another-key');

if ($item->relation('kind', $anotherItem)->put()) {
    // success
}

// TIP: Relations are one way operations. We relate an item to another,
// but that other item doesn't automatically gets related back to the calling item.

// To make life easier we implemented that two-way operation, so both source
// and destination items relates to each other.
// Just pass 'true' as parameter.

if ($item->relation('kind', $anotherItem)->put(true)) {
    // success, now both items are related to each other

    // Note that 2 API calls are made in this operation,
    // and the operation success is given only if both are
    // successful.
}

```


### Graph Delete

Deletes a relationship between two objects. Relations don't have a history, so the operation have the purge=true parameter.

```php
// Approach 1 - Client
$item = $client->deleteRelation('collection', 'key', 'kind', 'toCollection', 'toKey');

// Approach 2 - Object
$item = $collection->item('key');
$anotherItem = $collection->item('another-key');

if ($item->relation('kind', $anotherItem)->delete()) {
    // success
}

// Same two-way operation can be made here too:
if ($item->relation('kind', $anotherItem)->delete(true)) {
    // success, now both items are not related to each other anymore
}

```




## Docs

Please refer to the source code for now, while a proper documentation is made.


## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- When adding a field for a date, suffix it with '_date' or other [supported prefixes](https://orchestrate.io/docs/apiref#sorting-by-date);
- Avoid using dashes in properties names, not required, but makes easier to be accessed directly in JS or PHP, without need to wrap in item['my-prop'] or item->{'my-prop'};
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list;



## Postscript

This client is actively maintained. I am using to develop the next version of [typo/graphic posters](https://www.typographicposters.com) and should be using in more projects at work.

That project is on [Phalcon](http://phalconphp.com/en/) so any heads up into creating a proper ODM for Orchestrate are appreciated.  

