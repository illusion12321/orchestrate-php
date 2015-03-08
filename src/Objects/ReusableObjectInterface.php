<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Defines an object as being reusable. We can reset and init the object without creating a new instance.
 */
interface ReusableObjectInterface
{
    /**
     * Resets the current instance to its initial state.
     */
    public function reset();

    /**
     * Single entry point to initialize the current instance
     * and its properties.
     * 
     * @param array $values
     */
    public function init(array $values);
}
