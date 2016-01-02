<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ToArrayInterface;
use andrefelipe\Orchestrate\Common\ToJsonInterface;

/**
 * Defines the basis for all our objects. They should:
 * - Accessible
 * - Reusable: We can reset and init the object without creating a new instance.
 */
interface ObjectInterface extends
\ArrayAccess,
\Serializable,
\JsonSerializable,
ToArrayInterface, 
ToJsonInterface,
ConnectionInterface
{
    /**
     * Gets the kind of the current object. Value is immutable, it's the same as:
     * <code>
     * echo $my_object::KIND;
     * </code>
     * 
     * This value matches the Orchestrate kind property for singular items 
     * (KeyValue, Event and Relationship) and adds our list classes for internal
     * control (Application, Collection, Refs, Events and Relationships).
     *
     * @return string
     */
    public function getKind();

    /**
     * Resets the current instance to its initial state.
     */
    public function reset();

    /**
     * Single entry point to initialize the current instance and its properties.
     * Should be compatible with the toArray output.
     *
     * @param array $data
     */
    public function init(array $data);
}
