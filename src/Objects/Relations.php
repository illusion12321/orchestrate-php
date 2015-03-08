<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\KindTrait;

class Relations extends AbstractList
{
    use KeyTrait;
    use KindTrait;

    public function __construct($collection = null, $key = null, $kind = null)
    {
        parent::__construct($collection);
        $this->setKey($key);
        $this->setKind($kind);
    }

    /**
     * @param int $limit
     * @param int $offset
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get($limit = 10, $offset = 0)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relations/'.$this->getKind(true);

        $parameters = ['limit' => $limit];
        
        if ($offset) {
            $parameters['offset'] = $offset;
        }            
       
        // request
        $this->request('GET', $path, ['query' => $parameters]);

        return $this->isSuccess();
    }
}
