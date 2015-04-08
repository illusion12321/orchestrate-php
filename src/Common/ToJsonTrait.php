<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait that implements the toJson method.
 * Implementation should also add the ToJsonInterface.
 *
 * @internal
 */
trait ToJsonTrait
{
    public function toJson($options = 0, $depth = 512)
    {
        // depth was only added on php 5.5
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            return json_encode($this->toArray(), $options, $depth);
        }

        return json_encode($this->toArray(), $options);
    }
}
