<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ToArrayInterface;

/**
 * Defines an object as being reusable. We can reset and init the object without creating a new instance.
 */
interface ReusableObjectInterface extends ToArrayInterface
{
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
