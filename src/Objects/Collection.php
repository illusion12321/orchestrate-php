<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Collection extends AbstractList
{
    use Properties\EventClassTrait;
    use Properties\ItemClassTrait;
    use Properties\AggregatesTrait;

    /**
     * Constructs an item instance. A KeyValue or a custom class you set with
     * setItemClass().
     *
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     */
    public function item($key = null, $ref = null)
    {
        return $this->getItemClass()->newInstance()
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setRef($ref)
            ->setHttpClient($this->getHttpClient());
    }

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
    public function event($key = null, $type = null, $timestamp = null, $ordinal = null)
    {
        return $this->getEventClass()->newInstance()
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setType($type)
            ->setTimestamp($timestamp)
            ->setOrdinal($ordinal)
            ->setHttpClient($this->getHttpClient());
    }

    /**
     *
     * @return Events
     */
    public function events($key = null, $type = null)
    {
        return (new Events())
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setType($type)
            ->setHttpClient($this->getHttpClient());
    }

    /**
     * Gets total item count of the Collection.
     *
     * May return zero if request was unsuccesful, in which case you can check
     * the response with "getResponse" or the aliases "getStatusCode/getStatus".
     *
     * @return int|null Item count, or null on request failure.
     */
    public function getTotalItems()
    {
        // makes a straight Search query for no results
        $path = $this->getCollection(true);
        $parameters = [
            'query' => '@path.kind:item',
            'limit' => 0,
        ];
        parent::request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $body = $this->getBody();
            if (isset($body['total_count'])) {
                return (int) $body['total_count'];
            }
        }
        return null;
    }

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
    public function getTotalEvents($type = null)
    {
        // makes a straight Search query for no results
        $path = $this->getCollection(true);
        $parameters = [
            'query' => '@path.kind:event',
            'limit' => 0,
        ];

        if ($type) {
            $parameters['query'] .= ' AND @path.type:'.$type;
        }

        parent::request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $body = $this->getBody();
            if (isset($body['total_count'])) {
                return (int) $body['total_count'];
            }
        }
        return null;
    }

    public function reset()
    {
        parent::reset();
        $this->_aggregates = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['itemClass'])) {
                $this->setItemClass($data['itemClass']);
            }
            if (!empty($data['eventClass'])) {
                $this->setEventClass($data['eventClass']);
            }
            if (!empty($data['aggregates'])) {
                $this->_aggregates = new ObjectArray($data['aggregates']);
            }

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = 'collection';

        if ($this->getItemClass()->name !== self::$defaultItemClassName) {
            $data['itemClass'] = $this->getItemClass()->name;
        }
        if ($this->getEventClass()->name !== self::$defaultEventClassName) {
            $data['eventClass'] = $this->getEventClass()->name;
        }
        if ($this->_aggregates) {
            $data['aggregates'] = $this->_aggregates->toArray();
        }

        return $data;
    }

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
    public function get($limit = 10, KeyRangeBuilder $range = null)
    {
        // define request options
        $parameters = $range ? $range->toArray() : [];
        $parameters['limit'] = $limit > 100 ? 100 : $limit;

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    /**
     * Deletes a collection. Warning this will permanently erase all data within
     * this collection and cannot be reversed!
     *
     * To prevent accidental deletions, provide the current collection name as
     * the parameter. The collection will only be deleted if both names match.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function delete($collectionName)
    {
        if ($collectionName === $this->getCollection(true)) {

            $this->request('DELETE', $this->getCollection(), ['query' => ['force' => 'true']]);

            if ($this->isSuccess()) {
                $this->setResponseValues();
            }
            return $this->getStatusCode() === 204;
        }

        return false;
    }

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $parameters = [
            'query' => $query,
            'limit' => $limit,
        ];
        if (!empty($sort)) {
            $parameters['sort'] = implode(',', (array) $sort);
        }
        if (!empty($aggregate)) {
            $parameters['aggregate'] = implode(',', (array) $aggregate);
        }
        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    /**
     * Adds aggregates support.
     */
    protected function setResponseValues()
    {
        parent::setResponseValues();

        if ($this->isSuccess()) {
            $body = $this->getBody();
            if (!empty($body['aggregates'])) {
                $this->_aggregates = new ObjectArray($body['aggregates']);
            }
        }
    }

    /**
     * @param array $itemValues
     */
    protected function createInstance(array $itemValues)
    {
        if (!empty($itemValues['path']['kind'])) {
            $kind = $itemValues['path']['kind'];

            if ($kind === 'item') {
                $class = $this->getItemClass();
                $item = $class->newInstance()->init($itemValues);

            } elseif ($kind === 'event') {
                $class = $this->getEventClass();
                $item = $class->newInstance()->init($itemValues);

            } elseif ($kind === 'relationship') {
                $item = (new Relationship())->init($itemValues);

            } else {
                return null;
            }

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
