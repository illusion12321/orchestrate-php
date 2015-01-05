<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\AbstractList;
use andrefelipe\Orchestrate\Objects\Common\KeyTrait;

class Relations extends AbstractList
{
    use KeyTrait;
    

    public function __construct($collection, $key=null)
    {
        parent::__construct($collection);
        $this->key = $key;
    }

    

    /**
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     * @return Relations self
     */
    public function listRelations($kind, $limit=10, $offset=0)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relations/';
        $path .= implode('/', (array) $kind);

        $parameters = ['limit' => $limit];
        
        if ($offset)
            $parameters['offset'] = $offset;
       
        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this;
    }


    



}