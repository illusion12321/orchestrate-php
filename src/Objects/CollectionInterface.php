<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

/**
 * Define the Collection minimum required interface.
 */
interface CollectionInterface extends ListInterface
{
    const KIND = 'collection';

    /**
     * @param boolean $required
     *
     * @return string
     */
    public function getCollection($required = false);

    /**
     * @param string $collection
     *
     * @return self
     */
    public function setCollection($collection);

    /**
     * @return ObjectArray
     */
    public function getAggregates();

    /**
     * Constructs an item instance. A KeyValue or a custom class you set with
     * setItemClass().
     *
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     */
    public function item($key = null, $ref = null);

    /**
     * Constructs an event instance. An Event or a custom class you set with
     * setEventClass().
     *
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     *
     * @return EventInterface
     */
    public function event($key = null, $type = null, $timestamp = null, $ordinal = null);

    /**
     *
     * @return Events
     */
    public function events($key = null, $type = null);

    /**
     * Gets total item count of the Collection.
     *
     * May return zero if request was unsuccesful, in which case you can check
     * the response with "getResponse" or the aliases "getStatusCode/getStatus".
     *
     * @return int|null Item count or null on request failure.
     */
    public function getTotalItems();

    /**
     * Gets total event count of the Collection.
     *
     * May return zero if request was unsuccesful, in which case you can check
     * the response with "getResponse" or the aliases "getStatusCode/getStatus".
     *
     * @param string $type Optionally restrict to a specific event type.
     *
     * @return int|null Event count, or null on request failure.
     */
    public function getTotalEvents($type = null);

    /**
     * Deletes a collection. Warning this will permanently erase all data within
     * this collection and cannot be reversed!
     *
     * To prevent accidental deletions, provide the current collection name as
     * the parameter. The collection will only be deleted if both names match.
     *
     * @param string $collectionName The collection name.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function delete($collectionName);

    /**
     * Gets a lexicographically ordered list of items contained in a collection,
     * specified by the limit and key range parameters.
     *
     * If there are more results available, the pagination URL can be checked
     * with getNextUrl/getPrevUrl, and queried with nextPage/prevPage methods.
     *
     * @param int $limit The limit of items to return. Defaults to 10 and max to 100.
     * @param KeyRangeBuilder $range
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function get($limit = 10, KeyRangeBuilder $range = null);

    /**
     * Search!
     *
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0);
}
