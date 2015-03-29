<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

class Events extends AbstractList
{
    use EventClassTrait;
    use KeyTrait;
    use TypeTrait;

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
     * @return int
     */
    public function getTotalCount()
    {
        if ($this->_totalCount === null) {

            // makes a straight Search query for no results
            $path = $this->getCollection(true);
            $parameters = [
                'query' => '@path.kind:event',
                'limit' => 0,
            ];
            $response = $this->getHttpClient(true)->request('GET', $path, ['query' => $parameters]);

            // set value if succesful
            if ($response->getStatusCode() === 200) {
                $body = $response->json();
                $this->_totalCount = !empty($body['total_count']) ? (int) $body['total_count'] : 0;
            }
        }
        return $this->_totalCount;
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_type = null;
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

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = 'events';
        $data['eventClass'] = $this->getEventClass()->name;

        if (!empty($this->_key)) {
            $data['key'] = $this->_key;
        }
        if (!empty($this->_type)) {
            $data['type'] = $this->_type;
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

        return $this->isSuccess();
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
