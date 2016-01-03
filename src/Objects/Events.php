<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

class Events extends AbstractList implements EventsInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\TypeTrait;
    use Properties\AggregatesTrait;
    use Properties\EventClassTrait;

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     */
    public function __construct($collection = null, $key = null, $type = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_type = null;
        $this->_aggregates = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (isset($data['eventClass'])) {
                $this->setEventClass($data['eventClass']);
            }
            if (isset($data['collection'])) {
                $this->setCollection($data['collection']);
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
        $data['kind'] = static::KIND;
        $data['collection'] = $this->_collection;
        $data['key'] = $this->_key;
        $data['type'] = $this->_type;

        if ($this->getEventClass()->name !== self::$defaultEventClassName) {
            $data['eventClass'] = $this->getEventClass()->name;
        }
        if ($this->_aggregates) {
            $data['aggregates'] = $this->_aggregates->toArray();
        }

        return $data;
    }

    public function get($limit = 10, TimeRangeBuilder $range = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
        .'/events/'.$this->getType(true).'/';

        $parameters = $range ? $range->toArray() : [];
        $parameters['limit'] = $limit > 100 ? 100 : $limit;

        // request
        $this->request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }

        return $this->isSuccess();
    }

    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $queryParts = ['@path.kind:event'];
        if (!empty($this->_key)) {
            $queryParts[] = '@path.key:'.$this->_key;
        }
        if (!empty($this->_type)) {
            $queryParts[] = '@path.type:'.$this->_type;
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
            } else {
                $this->_aggregates = null;
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
