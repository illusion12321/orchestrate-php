<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\ApplicationTrait;
use andrefelipe\Orchestrate\Objects\Common\CollectionTrait;
use GuzzleHttp\HasDataTrait;
use GuzzleHttp\ToArrayInterface;

abstract class AbstractObject extends AbstractResponse implements
    ToArrayInterface,
    \ArrayAccess,
    \IteratorAggregate,
    \Countable
{
    use ApplicationTrait;
    use CollectionTrait;
    use HasDataTrait;
    
    
    public function __construct($collection)
    {        
        $this->collection = $collection;
    }


    
    protected function request($method, $url = null, array $options = [])
    {
        // request at the Application HTTP client
        $response = $this->getApplication()->request($method, $url, $options);

        // and store/process the results
        $this->setResponse($response);
    }



}