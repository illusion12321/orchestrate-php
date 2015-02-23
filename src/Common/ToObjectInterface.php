<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * An object that can be represented as an array
 */
interface ToObjectInterface extends ToArrayInterface
{
    /**
     * Get the object representation of an object.
     * Uses toArray method.
     *
     * @return ObjectArray
     */
    public function toObject();

    /**
     * Get the JSON representation of an object.
     * Uses toArray method.
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}
