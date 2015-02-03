<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\KindTrait;

class Graph extends AbstractList
{
    use KeyTrait;
    use KindTrait;

    public function __construct($collection, $key = null, $kind = null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->setKind($kind);
    }

    /**
     * @param int $limit
     * @param int $offset
     * 
     * @return Graph self
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelations($limit = 10, $offset = 0)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/relations/'.implode('/', $this->getKind(true));
        $parameters = ['limit' => $limit];
        
        if ($offset)
            $parameters['offset'] = $offset;
       
        // request
        $this->request('GET', $path, ['query' => $parameters]);
        
        return $this;
    }
}
