<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;

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

    /**
     * @param int $limit
     * @param array $range
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function get($limit = 10, array $range = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/events/'.$this->getType(true).'/';
        
        $parameters = ['limit' => $limit];

        if ($range) {
            if (isset($range['start']))
                $parameters['startEvent'] = $range['start'];

            if (isset($range['after']))
                $parameters['afterEvent'] = $range['after'];

            if (isset($range['before']))
                $parameters['beforeEvent'] = $range['before'];

            if (isset($range['end']))
                $parameters['endEvent'] = $range['end'];
        }

        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this->isSuccess();
    }
}
