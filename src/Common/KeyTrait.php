<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait that implements the Key methods.
 * 
 * @internal
 */
trait KeyTrait
{
    /**
     * @var string
     */
    protected $key = null;
    
    /**
     * @param boolean $required 
     * 
     * @return string
     */
    public function getKey($required = false)
    {
        if ($required)
            $this->noKeyException();

        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = (string) $key;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'key' is not set yet.
     */
    protected function noKeyException()
    {
        if (!$this->key) {
            throw new \BadMethodCallException('There is no key set yet. Please do so through setKey() method.');
        }
    }    
}
