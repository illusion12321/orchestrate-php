<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Timestamp methods.
 * 
 * @internal
 */
trait TimestampTrait
{
    /**
     * @var int
     */
    private $_timestamp = 0;
    
    /**
     * @param boolean $required
     * 
     * @return int
     */
    public function getTimestamp($required = false)
    {
        if ($required)
            $this->noTimestampException();

        return $this->_timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->_timestamp = (int) $timestamp;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'timestamp' is not set yet.
     */
    protected function noTimestampException()
    {
        if (!$this->_timestamp) {
            throw new \BadMethodCallException('There is no timestamp set yet. Please do so through setTimestamp() method.');
        }
    }    
}
