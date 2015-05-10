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
        return json_encode($this->toArray(), $options, $depth);
    }
}
