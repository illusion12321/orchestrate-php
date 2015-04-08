<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * An object that can be represented as an array
 */
interface ToJsonInterface extends ToArrayInterface
{
    /**
     * Get the JSON representation of an object. Uses toArray output.
     * 
     * For PHP version lower than 5.5 the depth parameter is ignored.
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}
