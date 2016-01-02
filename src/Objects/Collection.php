<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Collection extends AbstractList implements CollectionInterface
{
    use Properties\CollectionTrait;
    use Properties\EventClassTrait;
    use Properties\ItemClassTrait;
    use Properties\AggregatesTrait;

    /**
     * @param string $collection
     */
    public function __construct($collection = null)
    {
        $this->setCollection($collection);
    }

    public function item($key = null, $ref = null)
    {
        return $this->getItemClass()->newInstance()
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setRef($ref)
            ->setHttpClient($this->getHttpClient());
    }

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

    public function events($key = null, $type = null)
    {
        return (new Events())
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setType($type)
            ->setHttpClient($this->getHttpClient());
    }

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
        $data['kind'] = static::KIND;

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

    public function delete($collectionName)
    {
        if ($collectionName === $this->getCollection(true)) {

            $this->request('DELETE', $collectionName, ['query' => ['force' => 'true']]);

            if ($this->isSuccess()) {
                $this->setResponseValues();
            }
            return $this->getStatusCode() === 204;
        }

        return false;
    }

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
