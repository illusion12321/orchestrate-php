<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Aggregates methods.
 *
 * @internal
 */
trait AggregatesTrait
{
    /**
     * @var ObjectArray
     */
    private $_aggregates;

    /**
     * @return ObjectArray
     */
    public function getAggregates()
    {
        if (!$this->_aggregates) {
            $this->_aggregates = new ObjectArray();
        }
        return $this->_aggregates;
    }
}
