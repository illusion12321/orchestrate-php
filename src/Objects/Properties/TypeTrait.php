<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Type methods.
 * 
 * @internal
 */
trait TypeTrait
{
    /**
     * @var string
     */
    private $_type = null;

    /**
     * @param boolean $required
     * 
     * @return string
     */
    public function getType($required = false)
    {
        if ($required)
            $this->noTypeException();

        return $this->_type;
    }

    /**
     * @param string $type
     * 
     * @return self
     */
    public function setType($type)
    {
        $this->_type = (string) $type;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'type' is not set yet.
     */
    protected function noTypeException()
    {
        if (!$this->_type) {
            throw new \BadMethodCallException('There is no type set yet. Please do so through setType() method.');
        }
    }    
}
