<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


class Events extends AbstractList
{

    /**
     * @var string
     */
    protected $key;




    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }




    /**
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * @return Refs self
     */
    public function listEvents($type, $limit=10, $startEvent='', $afterEvent='', $beforeEvent='', $endEvent='')
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        
        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$type.'/';
        
        $parameters = ['limit' => $limit];

        if ($startEvent)
            $parameters['startEvent'] = $startEvent;

        if ($afterEvent)
            $parameters['afterEvent'] = $afterEvent;

        if ($beforeEvent)
            $parameters['beforeEvent'] = $beforeEvent;

        if ($endEvent)
            $parameters['endEvent'] = $endEvent;       


        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this;
    }


    



}