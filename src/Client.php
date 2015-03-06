<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Collection;
use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\Refs;
use andrefelipe\Orchestrate\Objects\Event;
use andrefelipe\Orchestrate\Objects\Events;
use andrefelipe\Orchestrate\Objects\Graph;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Client extends AbstractClient
{
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

    /**
     * @param string $collection
     * @param int $limit
     * @param array $range
     * 
     * @return Collection
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection($collection, $limit = 10, array $range = null)
    {
        $list = (new Collection($collection))
            ->setClient($this)
            ->setChildClass($this->getItemClass());

        $list->get($limit, $range);
        return $list;
    }

    /**
     * @param string $collection
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     * 
     * @return Collection
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($collection, $query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        $list = (new Collection($collection))
            ->setClient($this)
            ->setChildClass($this->getItemClass());
        
        $list->search($query, $sort, $aggregate, $limit, $offset);
        return $list;
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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection);

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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection, $key);

        $item->purge();
        return $item;
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
            ->setClient($this)
            ->setChildClass($this->getItemClass());

        $list->listRefs($limit, $offset, $values);
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
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

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
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

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
    public function postEvent($collection, $key, $type, array $value, $timestamp = null)
    {
        $item = newEvent($collection, $key, $type);

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
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

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
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

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
            ->setClient($this)
            ->setChildClass($this->getEventClass());

        $list->get($limit, $range);
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
        $item = $this->newItem($collection, $key);

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
        $item = $this->newItem($collection, $key);

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
            ->setClient($this)
            ->setChildClass($this->getItemClass());

        $list->listRelations($limit, $offset);
        return $list;
    }

    private function newItem($collection = null, $key = null, $ref = null)
    {
        return $this->getItemClass()
            ->newInstance($collection, $key, $ref)
            ->setClient($this);
    }

    private function newEvent(
        $collection = null,
        $key = null,
        $type = null,
        $timestamp = null,
        $ordinal = null
    )
    {
        return $this->getEventClass()
            ->newInstance(
                $collection,
                $key,
                $type,
                $timestamp,
                $ordinal
            )->setClient($this);
    }
}
