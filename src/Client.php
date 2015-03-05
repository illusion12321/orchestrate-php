<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\KeyValues;
use andrefelipe\Orchestrate\Objects\Refs;
use andrefelipe\Orchestrate\Objects\Search;
use andrefelipe\Orchestrate\Objects\Event;
use andrefelipe\Orchestrate\Objects\Events;
use andrefelipe\Orchestrate\Objects\Graph;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Client extends Application
{
	public function __construct($apiKey = null, $host = null, $apiVersion = null)
	{
        parent::__construct($apiKey, $host, $apiVersion);
	}


    // Collection

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function deleteCollection($collection)
    {
        $response = $this->request(
            'DELETE',
            $collection,
            ['query' => ['force' => 'true']]
        );

        return $response->getStatusCode() === 204;
    }


    // Key/Value

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($collection, $key, $ref = null)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->get($ref);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param array $value
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put($collection, $key, array $value, $ref = null)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->put($value, $ref);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch($collection, $key, PatchBuilder $operations, $ref = null, $reload = false)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->patch($operations, $ref, $reload);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge($collection, $key, array $value, $ref = null, $reload = false)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->patchMerge($value, $ref, $reload);
        return $item;
    }

    /**
     * @param string $collection
     * @param array $value
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post($collection, array $value)
    {
        $item = (new KeyValue($collection))
            ->setApplication($this);

        $item->post($value);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($collection, $key, $ref = null)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->delete($ref);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge($collection, $key)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->purge();
        return $item;
    }

    /**
     * @param string $collection
     * @param int $limit
     * @param array $range
     * 
     * @return KeyValues
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection($collection, $limit = 10, array $range = null)
    {
        $list = (new KeyValues($collection))
            ->setApplication($this);

        $list->listCollection($limit, $range);
        return $list;
    }
    

    // Refs

    /**
     * @param string $collection
     * @param string $key
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * 
     * @return Refs
     * @link https://orchestrate.io/docs/apiref#refs-list
     */
    public function listRefs($collection, $key, $limit = 10, $offset = 0, $values = false)
    {
        $list = (new Refs($collection, $key))
            ->setApplication($this);

        $list->listRefs($limit, $offset, $values);
        return $list;
    }


    // Search

    /**
     * @param string $collection
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     * 
     * @return Search
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($collection, $query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        $list = (new Search($collection))
            ->setApplication($this);

        $list->search($query, $sort, $aggregate, $limit, $offset);
        return $list;
    }


    // Events

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function getEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        $item = (new Event($collection, $key, $type, $timestamp, $ordinal))
            ->setApplication($this);

        $item->get();
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param array $value
     * @param string $ref
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function putEvent($collection, $key, $type, $timestamp, $ordinal, array $value, $ref = null)
    {
        $item = (new Event($collection, $key, $type, $timestamp, $ordinal))
            ->setApplication($this);

        $item->put($value, $ref);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param array $value
     * @param int $timestamp
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function postEvent($collection, $key, $type, array $value, $timestamp = 0)
    {
        $item = (new Event($collection, $key, $type))
            ->setApplication($this);

        $item->post($value, $timestamp);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param string $ref
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function deleteEvent($collection, $key, $type, $timestamp, $ordinal, $ref = null)
    {
        $item = (new Event($collection, $key, $type, $timestamp, $ordinal))
            ->setApplication($this);

        $item->delete($ref);
        return $item;
    }

   /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function purgeEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        $item = (new Event($collection, $key, $type, $timestamp, $ordinal))
            ->setApplication($this);

        $item->purge();
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $limit
     * @param array $range
     * 
     * @return Events
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function listEvents($collection, $key, $type, $limit = 10, array $range = null)
    {
        $list = (new Events($collection, $key, $type))
            ->setApplication($this);

        $list->listEvents($limit, $range);
        return $list;
    }


    // Graph

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelation($collection, $key, $kind, $toCollection, $toKey)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->putRelation($kind, $toCollection, $toKey);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelation($collection, $key, $kind, $toCollection, $toKey)
    {
        $item = (new KeyValue($collection, $key))
            ->setApplication($this);

        $item->deleteRelation($kind, $toCollection, $toKey);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     * 
     * @return Graph
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelations($collection, $key, $kind, $limit = 10, $offset = 0)
    {
        $list = (new Graph($collection, $key, $kind))
            ->setApplication($this);

        $list->listRelations($limit, $offset);
        return $list;
    }
}
