<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Ordinal methods.
 * 
 * @internal
 */
trait OrdinalTrait
{
    /**
     * @var int
     */
    protected $ordinal = 0;
    
    /**
     * @param boolean $required
     * 
     * @return int
     */
    public function getOrdinal($required = false)
    {
        if ($required)
            $this->noOrdinalException();

        return $this->ordinal;
    }

    /**
     * @param int|string $ordinal
     */
    public function setOrdinal($ordinal)
    {
        $this->ordinal = (int) $ordinal;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'ordinal' is not set yet.
     */
    protected function noOrdinalException()
    {
        if (!$this->ordinal) {
            throw new \BadMethodCallException('There is no ordinal set yet. Please do so through setOrdinal() method.');
        }
    }    
}
