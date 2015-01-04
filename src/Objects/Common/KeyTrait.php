<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Key methods
 */
trait KeyTrait
{

    /**
     * @var string
     */
    protected $key = null;
    

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }


    protected function noKeyException()
    {
        if (!$this->key) {
            throw new \BadMethodCallException('There is no key set yet. Please do so through setKey() method.');
        }
    }
    
}