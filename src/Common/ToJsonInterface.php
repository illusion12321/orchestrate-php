<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * An object that can be represented as an array
 */
interface ToJsonInterface extends ToArrayInterface
{
    /**
     * Get the JSON representation of an object.
     * Uses toArray method.
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}
