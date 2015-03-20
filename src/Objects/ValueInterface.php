<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 *
 */
interface ValueInterface
{
    /**
     * Get item Value.
     *
     * @return ObjectArray
     */
    public function getValue();

    /**
     * Set item Value. Overwrites properties if already set.
     *
     * @param array $values
     */
    public function setValue(array $values);

    /**
     * Recursively merge one item Value into another.
     *
     * @param ValueInterface $object
     */
    public function mergeValue(ValueInterface $object);

    /**
     * Sets all public properties to null.
     */
    public function resetValue();

    /**
     * Use a JMESPath expression to model the data you need.
     */
    public function extractValue($expression);
}
