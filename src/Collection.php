<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Common\ApplicationTrait;
use andrefelipe\Orchestrate\Objects\Common\CollectionTrait;

class Collection
{
    use ApplicationTrait;
    use CollectionTrait;


    public function __construct($collection)
    {
        $this->collection = $collection;
    }



    /**
     * @return boolean
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

        return $response->getStatusCode() === 200;
    }






    // Cross-object API

    // Key/Value

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
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
     * @return KeyValue
     */
    public function put($key, array $value, $ref=null)
    {
        return $this->getApplication()
            ->put($this->collection, $key, $value, $ref);
    }

    /**
     * @param array $value
     * @return KeyValue
     */
    public function post(array $value)
    {
        return $this->getApplication()
            ->post($this->collection, $value);
    }

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function delete($key, $ref=null)
    {
        return $this->getApplication()
            ->delete($this->collection, $key, $ref, $purge);
    }

    /**
     * @param string $key
     * @return KeyValue
     */
    public function purge($key)
    {
        return $this->getApplication()
            ->purge($this->collection, $key);
    }

    /**
     * @param int $limit
     * @param array $range
     * @return KeyValues
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
     * @return Refs
     */
    public function listRefs($key, $limit=10, $offset=0, $values=false)
    {
        return $this->getApplication()
            ->listRefs($this->collection, $key, $limit, $offset, $values);
    }


    // Search

    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search
     */
    public function search($query, $sort='', $limit=10, $offset=0)
    {
        return $this->getApplication()
            ->search($this->collection, $query, $sort, $limit, $offset);
    }


    // Events

    /**
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @return Events
     */
    public function listEvents($key, $type, $limit=10, array $range=null)
    {
        return $this->getApplication()
            ->listEvents($this->collection, $key, $type, $limit, $range);
    }

    
    

}