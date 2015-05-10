<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Objects\Properties\EventClassTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

class Events extends AbstractList
{
    use EventClassTrait;
    use KeyTrait;
    use TypeTrait;

    /**
     * @var ObjectArray
     */
    private $_aggregates;

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     */
    public function __construct($collection = null, $key = null, $type = null)
    {
        parent::__construct($collection);
        $this->setKey($key);
        $this->setType($type);
    }

    /**
     * Constructs an event instance. An Event or a custom class you set with setEventClass().
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
                    ->setHttpClient($this->getHttpClient(true));
    }

    /**
     * @return ObjectArray
     */
    public function getAggregates()
    {
        if (!$this->_aggregates) {
            $this->_aggregates = new ObjectArray();
        }
        return $this->_aggregates;
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_type = null;
        $this->_aggregates = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['eventClass'])) {
                $this->setEventClass($data['eventClass']);
            }
            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
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
        $data['kind'] = 'events';

        if ($this->getEventClass()->name !== self::$defaultEventClass) {
            $data['eventClass'] = $this->getEventClass()->name;
        }
        if (!empty($this->_key)) {
            $data['key'] = $this->_key;
        }
        if (!empty($this->_type)) {
            $data['type'] = $this->_type;
        }
        if ($this->_aggregates) {
            $data['aggregates'] = $this->_aggregates->toArray();
        }

        return $data;
    }

    /**
     * Gets a list of events in reverse chronological order,
     * specified by the limit and time range parameters.
     *
     * If there are more results available, the pagination URL can be checked with
     * getNextUrl/getPrevUrl, and queried with nextPage/prevPage methods.
     *
     * @param int $limit The limit of items to return. Defaults to 10 and max to 100.
     * @param TimeRangeBuilder $range
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function get($limit = 10, TimeRangeBuilder $range = null)
    {
        // define request options
        $path = $this->getCollection(true) . '/' . $this->getKey(true)
        . '/events/' . $this->getType(true) . '/';

        $parameters = $range ? $range->toArray() : [];
        $parameters['limit'] = $limit > 100 ? 100 : $limit;

        // request
        $this->request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }

        return $this->isSuccess();
    }

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-events
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $queryParts = ['@path.kind:event'];
        if (!empty($this->_key)) {
            $queryParts[] = '@path.key:' . $this->_key;
        }
        if (!empty($this->_type)) {
            $queryParts[] = '@path.type:' . $this->_type;
        }
        if ($query) {
            $queryParts[] = $query;
        }

        $parameters = [
            'query' => implode(' AND ', $queryParts),
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

            if ($kind === 'event') {
                $class = $this->getEventClass();

            } else {
                return null;
            }

            $item = $class->newInstance()->init($itemValues);

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
