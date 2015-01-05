<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Collection methods
 */
trait CollectionTrait
{

    /**
     * @var string
     */
    protected $collection;
    

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }
    

    protected function noCollectionException()
    {
        if (!$this->collection) {
            throw new \BadMethodCallException('There is no collection set yet. Please do so through setCollection() method.');
        }
    }
    
}