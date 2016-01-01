<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ToJsonInterface;

/**
 * Define the Relationship minimum required interface.
 */
interface RelationshipInterface extends
\ArrayAccess,
ValueInterface,
ToJsonInterface,
ReusableObjectInterface,
SearchableInterface,
ConnectionInterface
{
    const KIND = 'relationship';

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     */
    public function getSource($required = false);

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setSource(KeyValueInterface $item);

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     * @throws \BadMethodCallException if 'destination' is required but not set yet.
     */
    public function getDestination($required = false);

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setDestination(KeyValueInterface $item);

    /**
     * Get the relation kind between the objects.
     *
     * @param boolean $required
     *
     * @return string
     * @throws \BadMethodCallException if 'relation' is required but not set yet.
     */
    public function getRelation($required = false);

    /**
     * @param string $kind
     *
     * @return Relation self
     * @throws \InvalidArgumentException if 'kind' is array. Only one relation can be handled per time.
     */
    public function setRelation($kind);

    /**
     * @return string
     */
    public function getRef($required = false);

    /**
     * @param string $ref
     *
     * @return self
     */
    public function setRef($ref);

    /**
     * @return int
     */
    public function getReftime();

    /**
     * @return float
     */
    // public function getDistance();

    /**
     * Get the current relation value.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get();

    /**
     * Set the relation between the two objects.
     * Optionally Use the third parameter, $both_ways, to set the relation both ways
     * (2 API calls will be made).
     *
     * @param array $value
     * @param string $ref
     * @param boolean $both_ways
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function put(array $value = null, $ref = null, $both_ways = false)

    /**
     * Remove the relation between the two objects.
     * Use the $both_ways parameter to remove the relation both ways
     * (2 API calls will be made).
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function delete($both_ways = false)

}
