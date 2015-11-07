<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * An object that can be represented as a JSON string.
 */
interface ToJsonInterface extends ToArrayInterface
{
    /**
     * Get the JSON representation of an object. Uses the toArray output.
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}
