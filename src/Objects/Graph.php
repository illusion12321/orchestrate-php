<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;

class Graph extends AbstractList
{
    use KeyTrait;
        

    /**
     * @var array
     */
    protected $kind = null;
    

    


    public function __construct($collection, $key=null, $kind=null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->kind = (array) $kind;
    }


    /**
     * @return array
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param string|array $kind
     */
    public function setKind($kind)
    {
        $this->kind = (array) $kind;

        return $this;
    }




    /**
     * @param int $limit
     * @param int $offset
     * @return Graph self
     */
    public function listRelations($limit=10, $offset=0)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noKindException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relations/'.implode('/', $this->kind);
        $parameters = ['limit' => $limit];
        
        if ($offset)
            $parameters['offset'] = $offset;
       
        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this;
    }





    protected function noKindException()
    {
        if (empty($this->kind)) {
            throw new \BadMethodCallException('There is no kind set yet. Please do so through setKind() method.');
        }
    }



}