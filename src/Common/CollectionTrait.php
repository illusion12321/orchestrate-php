<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait that implements the Collection methods.
 * 
 * @internal
 */
trait CollectionTrait
{
    /**
     * @var string
     */
    private $_collection;

    /**
     * @param boolean $required 
     * 
     * @return string
     */
    public function getCollection($required = false)
    {
        if ($required)
            $this->noCollectionException();

        return $this->_collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->_collection = (string) $collection;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'collection' is not set yet.
     */
    protected function noCollectionException()
    {
        if (!$this->_collection) {
            throw new \BadMethodCallException('There is no collection set yet. Please do so through setCollection() method.');
        }
    }
}
