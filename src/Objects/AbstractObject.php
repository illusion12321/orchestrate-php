<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ApplicationTrait;
use andrefelipe\Orchestrate\Common\CollectionTrait;
use andrefelipe\Orchestrate\Common\ArrayAdapterTrait;
use andrefelipe\Orchestrate\Common\ToArrayInterface;

abstract class AbstractObject extends AbstractResponse implements
    ToArrayInterface,
    \ArrayAccess,
    \IteratorAggregate,
    \Countable
{
    use ApplicationTrait;
    use CollectionTrait;
    use ArrayAdapterTrait;
    
    /**
     * @param string $collection
     */
    public function __construct($collection)
    {
        $this->setCollection($collection);
    }

    protected function request($method, $url = null, array $options = [])
    {
        // request at the Application HTTP client
        $response = $this->getApplication(true)->request($method, $url, $options);

        // and store/process the results
        $this->setResponse($response);
    }
}
