<?php
namespace andrefelipe\Orchestrate\Objects\Common;

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
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param int|string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = is_string($timestamp) ? strtotime($timestamp) : (int) $timestamp;
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