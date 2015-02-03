<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Common\ApplicationTrait;
use andrefelipe\Orchestrate\Objects\Common\CollectionTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Collection
{
    use ApplicationTrait;
    use CollectionTrait;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }


    // Collection
    
    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function deleteCollection()
    {
        // required values
        $this->noCollectionException();

        // request
        $response = $this->getApplication()->request(
            'DELETE',
            $this->collection,
            ['query' => ['force' => 'true']]
        );

        return $response->getStatusCode() === 204;
    }


    // Key/Value

    /**
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($key, $ref=null)
    {
        return $this->getApplication()
            ->get($this->collection, $key, $ref);
    }

    /**
     * @param string $key
     * @param array $value
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put($key, array $value, $ref=null)
    {
        return $this->getApplication()
            ->put($this->collection, $key, $value, $ref);
    }

    /**
     * @param string $key
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch($key, PatchBuilder $operations, $ref=null, $reload=false)
    {
        return $this->getApplication()
            ->patch($this->collection, $key, $operations, $ref, $reload);
    }

    /**
     * @param string $key
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge($key, array $value, $ref=null, $reload=false)
    {
        return $this->getApplication()
            ->patchMerge($this->collection, $key, $value, $ref, $reload);
    }

    /**
     * @param array $value
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value)
    {
        return $this->getApplication()
            ->post($this->collection, $value);
    }

    /**
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($key, $ref=null)
    {
        return $this->getApplication()
            ->delete($this->collection, $key, $ref, $purge);
    }

    /**
     * @param string $key
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge($key)
    {
        return $this->getApplication()
            ->purge($this->collection, $key);
    }

    /**
     * @param int $limit
     * @param array $range
     * 
     * @return KeyValues
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection($limit=10, array $range=null)
    {
        return $this->getApplication()
            ->listCollection($this->collection, $limit, $range);
    }


    // Refs
    
    /**
     * @param string $key
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * 
     * @return Refs
     * @link https://orchestrate.io/docs/apiref#refs-list
     */
    public function listRefs($key, $limit=10, $offset=0, $values=false)
    {
        return $this->getApplication()
            ->listRefs($this->collection, $key, $limit, $offset, $values);
    }


    // Search

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     * 
     * @return Search
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)
    {
        return $this->getApplication()
            ->search($this->collection, $query, $sort, $aggregate, $limit, $offset);
    }


    // Events

    /**
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function getEvent($key, $type, $timestamp, $ordinal)
    {
        return $this->getApplication()
            ->getEvent($this->collection, $key, $type, $timestamp, $ordinal);
    }

    /**
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
    public function putEvent($key, $type, $timestamp, $ordinal, array $value, $ref=null)
    {
        return $this->getApplication()
            ->putEvent($this->collection, $key, $type, $timestamp, $ordinal, $value, $ref);
    }

    /**
     * @param string $key
     * @param string $type
     * @param array $value
     * @param int $timestamp
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function postEvent($key, $type, array $value, $timestamp=0)
    {
        return $this->getApplication()
            ->postEvent($this->collection, $key, $type, $value, $timestamp);
    }

    /**
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param string $ref
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function deleteEvent($key, $type, $timestamp, $ordinal, $ref=null)
    {
        return $this->getApplication()
            ->deleteEvent($this->collection, $key, $type, $timestamp, $ordinal, $ref);
    }

   /**
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * 
     * @return Event
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function purgeEvent($key, $type, $timestamp, $ordinal)
    {
        return $this->getApplication()
            ->purgeEvent($this->collection, $key, $type, $timestamp, $ordinal);
    }

    /**
     * @param string $key
     * @param string $type
     * @param int $limit
     * @param array $range
     * 
     * @return Events
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function listEvents($key, $type, $limit=10, array $range=null)
    {
        return $this->getApplication()
            ->listEvents($this->collection, $key, $type, $limit, $range);
    }

    
    // Graph

    /**
     * @param string $key
     * @param string $relation
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelation($key, $relation, $toCollection, $toKey)
    {
        return $this->getApplication()
            ->putRelation($this->collection, $key, $relation, $toCollection, $toKey);
    }

    /**
     * @param string $key
     * @param string $relation
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return KeyValue
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelation($key, $relation, $toCollection, $toKey)
    {
        return $this->getApplication()
            ->deleteRelation($this->collection, $key, $relation, $toCollection, $toKey);
    }

    /**
     * @param string $key
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     * 
     * @return Graph
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelations($key, $kind, $limit=10, $offset=0)
    {
        return $this->getApplication()
            ->listRelations($this->collection, $key, $kind, $limit, $offset);
    }
}
