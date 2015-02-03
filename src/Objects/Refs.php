<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;

class Refs extends AbstractList
{
    use KeyTrait;

    public function __construct($collection, $key=null)
    {
        parent::__construct($collection);
        $this->key = $key;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * 
     * @return Refs self
     * @link https://orchestrate.io/docs/apiref#refs-list
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

    protected function createChildrenClass(array $values)
    {
        return (new Ref($this->getCollection()))
            ->setApplication($this->getApplication())
            ->init($values);
    }
}
