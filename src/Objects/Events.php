<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\TypeTrait;

class Events extends AbstractList
{
    use KeyTrait;
    use TypeTrait;

    public function __construct($collection, $key = null, $type = null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->type = $type;
    }

    /**
     * @param int $limit
     * @param array $range
     * 
     * @return Events self
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function listEvents($limit = 10, array $range = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'.$this->getType(true).'/';
        
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
        
        return $this;
    }

    protected function createChildrenClass(array $values)
    {
        return (new Event($this->getCollection()))
            ->setApplication($this->getApplication())
            ->init($values);
    }
}
