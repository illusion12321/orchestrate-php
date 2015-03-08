<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

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
    private $_key = null;
    
    /**
     * @param boolean $required 
     * 
     * @return string
     */
    public function getKey($required = false)
    {
        if ($required){
            $this->noKeyException();
        }

        return $this->_key;
    }

    /**
     * @param string $key
     * 
     * @return self
     */
    public function setKey($key)
    {
        $this->_key = (string) $key;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'key' is not set yet.
     */
    protected function noKeyException()
    {
        if (!$this->_key) {
            throw new \BadMethodCallException('There is no key set yet. Please do so through setKey() method.');
        }
    }    
}
