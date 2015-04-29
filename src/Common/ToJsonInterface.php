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
     * For PHP version lower than 5.5 the depth parameter is ignored.
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}
