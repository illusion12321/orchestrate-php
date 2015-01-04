<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


class Refs extends AbstractList
{
    use KeyTrait;




    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
    }




    /**
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * @return Refs self
     */
    public function listRefs($limit=10, $offset=0, $values=false)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/refs/';
        
        $parameters = ['limit' => $limit];
        
        if ($offset)
            $parameters['offset'] = $offset;
       
        if ($values)
            $parameters['values'] = 'true';

        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this;
    }


    



}