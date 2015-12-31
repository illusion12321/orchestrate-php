<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

use andrefelipe\Orchestrate\Objects\KeyValueInterface;

/**
 * Trait that implements the Relation's source and destination methods.
 *
 * @internal
 */
trait RelationshipTrait
{
    /**
     * @var KeyValueInterface
     */
    private $_source = null;

    /**
     * @var KeyValueInterface
     */
    private $_destination = null;

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     */
    public function getSource($required = false)
    {
        if ($required && !$this->_source) {
            throw new \BadMethodCallException('There is no source set yet. Do so through setSource() method.');
        }

        return $this->_source;
    }

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setSource(KeyValueInterface $item)
    {
        $this->_source = $item;

        return $this;
    }

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     * @throws \BadMethodCallException if 'destination' is required but not set yet.
     */
    public function getDestination($required = false)
    {
        if ($required && !$this->_destination) {
            throw new \BadMethodCallException('There is no destination set yet. Do so through setDestination() method.');
        }

        return $this->_destination;
    }

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setDestination(KeyValueInterface $item)
    {
        $this->_destination = $item;

        return $this;
    }
}
