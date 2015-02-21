<?php
namespace andrefelipe\Orchestrate\Common;

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
    protected $timestamp = 0;
    
    /**
     * @param boolean $required
     * 
     * @return int
     */
    public function getTimestamp($required = false)
    {
        if ($required)
            $this->noTimestampException();

        return $this->timestamp;
    }

    /**
     * @param int|string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = is_string($timestamp) ? strtotime($timestamp) : (int) $timestamp;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'timestamp' is not set yet.
     */
    protected function noTimestampException()
    {
        if (!$this->timestamp) {
            throw new \BadMethodCallException('There is no timestamp set yet. Please do so through setTimestamp() method.');
        }
    }    
}
