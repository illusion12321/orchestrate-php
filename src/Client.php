<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\AbstractConnection;
use andrefelipe\Orchestrate\Objects\Collection;
use andrefelipe\Orchestrate\Objects\Event;
use andrefelipe\Orchestrate\Objects\Events;
use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\Properties\EventClassTrait;
use andrefelipe\Orchestrate\Objects\Properties\ItemClassTrait;
use andrefelipe\Orchestrate\Objects\Refs;
use andrefelipe\Orchestrate\Objects\Relation;
use andrefelipe\Orchestrate\Objects\Relations;
use andrefelipe\Orchestrate\Query\KeyRangeBuilder;
use andrefelipe\Orchestrate\Query\PatchBuilder;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Client interface for Orchestrate API.
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Client extends AbstractConnection
{
    use EventClassTrait;
    use ItemClassTrait;

    /**
     * Instantiates a default HTTP client on construction.
     *
     * @param string $apiKey Orchestrate API key. If not set gets from env 'ORCHESTRATE_API_KEY'.
     * @param string $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param string $version Orchestrate API version. Defaults to 'v0'
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        $config = default_http_config($apiKey, $host, $version);
        $this->setHttpClient(new GuzzleClient($config));
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->getHttpClient(true)->request('HEAD')->getStatusCode() === 200;
    }

    // Collection

    /**
     * Deletes a collection. Warning this will permanently erase all data within
     * this collection and cannot be reversed!
     *
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
     * @param KeyRangeBuilder $range
     *
     * @return Collection
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection(
                        $collection,
                        $limit = 10,
        KeyRangeBuilder $range = null
    ) {
        $list = (new Collection($collection))
            ->setItemClass($this->getItemClass())
            ->setEventClass($this->getEventClass())
            ->setHttpClient($this->getHttpClient(true));

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
    public function search(
        $collection,
        $query,
        $sort = null,
        $aggregate = null,
        $limit = 10,
        $offset = 0
    ) {
        $list = (new Collection($collection))
            ->setItemClass($this->getItemClass())
            ->setEventClass($this->getEventClass())
            ->setHttpClient($this->getHttpClient(true));

        $list->search($query, $sort, $aggregate, $limit, $offset);
        return $list;
    }

    // Key/Value

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
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
     * @return KeyValueInterface
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
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(
                     $collection,
                     $key,
        PatchBuilder $operations,
                     $ref = null,
                     $reload = false
    ) {
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
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(
              $collection,
              $key,
        array $value,
              $ref = null,
              $reload = false
    ) {
        $item = $this->newItem($collection, $key);

        $item->patchMerge($value, $ref, $reload);
        return $item;
    }

    /**
     * @param string $collection
     * @param array $value
     *
     * @return KeyValueInterface
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
     * @param array $values
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#bulk
     */
    public function bulk($collection, array $values)
    {
        $item = $this->newItem($collection);

        $item->bulk($values);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
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
     * @return KeyValueInterface
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
    public function listRefs(
        $collection,
        $key,
        $limit = 10,
        $offset = 0,
        $values = false
    ) {
        $list = (new Refs($collection, $key))
            ->setItemClass($this->getItemClass())
            ->setHttpClient($this->getHttpClient(true));

        $list->get($limit, $offset, $values);
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
     * @return EventInterface
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
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function putEvent(
              $collection,
              $key,
              $type,
              $timestamp,
              $ordinal,
        array $value,
              $ref = null
    ) {
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
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function postEvent(
              $collection,
              $key,
              $type,
        array $value,
              $timestamp = null
    ) {
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
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function deleteEvent(
        $collection,
        $key,
        $type,
        $timestamp,
        $ordinal,
        $ref = null
    ) {
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
     * @return EventInterface
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
     * @param TimeRangeBuilder $range
     *
     * @return Events
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function listEvents(
                         $collection,
                         $key,
                         $type,
                         $limit = 10,
        TimeRangeBuilder $range = null
    ) {
        $events = (new Events($collection))
            ->setKey($key)
            ->setType($type)
            ->setEventClass($this->getEventClass())
            ->setHttpClient($this->getHttpClient(true));

        $events->get($limit, $range);
        return $events;
    }

    /**
     * @param string $collection
     * @param string $type
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return Events
     * @link https://orchestrate.io/docs/apiref#search-events
     */
    public function searchEvents(
        $collection,
        $type,
        $query,
        $sort = null,
        $aggregate = null,
        $limit = 10,
        $offset = 0
    ) {
        $events = (new Events($collection))
            ->setType($type)
            ->setEventClass($this->getEventClass())
            ->setHttpClient($this->getHttpClient(true));

        $events->search($query, $sort, $aggregate, $limit, $offset);
        return $events;
    }

    // Graph

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @param boolean $bothWays
     *
     * @return Relation
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelation(
        $collection,
        $key,
        $kind,
        $toCollection,
        $toKey,
        $bothWays = false
    ) {
        $source = $this->newItem($collection, $key);
        $destination = $this->newItem($toCollection, $toKey);

        $relation = new Relation($source, $kind, $destination);
        $relation->put($bothWays);
        return $relation;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @param boolean $bothWays
     *
     * @return Relation
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelation(
        $collection,
        $key,
        $kind,
        $toCollection,
        $toKey,
        $bothWays = false
    ) {
        $source = $this->newItem($collection, $key);
        $destination = $this->newItem($toCollection, $toKey);

        $relation = new Relation($source, $kind, $destination);
        $relation->delete($bothWays);
        return $relation;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     *
     * @return Relations
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelations(
        $collection,
        $key,
        $kind,
        $limit = 10,
        $offset = 0
    ) {
        $list = (new Relations($collection, $key, $kind))
            ->setHttpClient($this->getHttpClient(true));

        $list->get($limit, $offset);
        return $list;
    }

    /**
     * Helper to create KeyValue instances.
     *
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     */
    private function newItem($collection = null, $key = null, $ref = null)
    {
        return $this->getItemClass()->newInstance()
            ->setCollection($collection)
            ->setKey($key)
            ->setRef($ref)
            ->setHttpClient($this->getHttpClient(true));
    }

    /**
     * Helper to create Event instances.
     *
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     *
     * @return EventInterface
     */
    private function newEvent(
        $collection = null,
        $key = null,
        $type = null,
        $timestamp = null,
        $ordinal = null
    ) {
        return $this->getEventClass()->newInstance()
            ->setCollection($collection)
            ->setKey($key)
            ->setType($type)
            ->setTimestamp($timestamp)
            ->setOrdinal($ordinal)
            ->setHttpClient($this->getHttpClient(true));
    }
}
