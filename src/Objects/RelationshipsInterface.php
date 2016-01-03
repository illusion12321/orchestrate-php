<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Define the Relationship list minimum required interface.
 */
interface RelationshipsInterface extends ListInterface
{
    const KIND = 'relationships';

    // /**
    //  * @param boolean $required
    //  *
    //  * @return KeyValueInterface
    //  */
    // public function getSource($required = false);

    // /**
    //  * @param KeyValueInterface $item
    //  *
    //  * @return self
    //  */
    // public function setSource(KeyValueInterface $item);

    // /**
    //  * @param boolean $required
    //  *
    //  * @return KeyValueInterface
    //  * @throws \BadMethodCallException if 'destination' is required but not set yet.
    //  */
    // public function getDestination($required = false);

    // /**
    //  * @param KeyValueInterface $item
    //  *
    //  * @return self
    //  */
    // public function setDestination(KeyValueInterface $item);

    // /**
    //  * Get the relation kind between the objects.
    //  *
    //  * @param boolean $required
    //  *
    //  * @return string
    //  * @throws \BadMethodCallException if 'relation' is required but not set yet.
    //  */
    // public function getRelation($required = false);

    // /**
    //  * @param string $kind
    //  *
    //  * @return Relation self
    //  * @throws \InvalidArgumentException if 'kind' is array. Only one relation can be handled per time.
    //  */
    // public function setRelation($kind);

    /**
     * @return ObjectArray
     */
    public function getAggregates();
    
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get($limit = 10, $offset = 0);
}
