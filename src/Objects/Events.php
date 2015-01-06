<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\TypeTrait;

class Events extends AbstractList
{
    use KeyTrait;
    use TypeTrait;



    public function __construct($collection, $key=null, $type=null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->type = $type;
    }



    /**
     * @param int $limit
     * @param array $range
     * @return Events self
     */
    public function listEvents($limit=10, array $range=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$this->type.'/';
        
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
        $this->request('GET', $path, ['query' => $parameters], 'Event');
        
        return $this;
    }


    



}