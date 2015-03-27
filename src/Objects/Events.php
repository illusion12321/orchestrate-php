<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

class Events extends AbstractList
{
    use KeyTrait;
    use TypeTrait;

    protected static $defaultChildClass = '\andrefelipe\Orchestrate\Objects\Event';

    protected static $minimumChildInterface = '\andrefelipe\Orchestrate\Objects\EventInterface';

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

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_type = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {
            parent::init($data);

            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
        }
        return $this;
    }

    public function toArray()
    {
        $result = parent::toArray();
        $result['kind'] = 'events';

        if (!empty($this->_key)) {
            $result['key'] = $this->_key;
        }
        if (!empty($this->_type)) {
            $result['type'] = $this->_type;
        }

        return $result;
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
}
