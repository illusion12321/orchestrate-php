<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Search Distance methods.
 *
 * @internal
 */
trait DistanceTrait
{
    /**
     * @var float
     */
    private $_distance = null;

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->_distance;
    }

    /**
     * @param float $value
     *
     * @return self
     */
    private function setDistance($value)
    {
        $this->_distance = (float) $value;

        return $this;
    }
}
