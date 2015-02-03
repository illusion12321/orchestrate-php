<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Value methods.
 * 
 * @internal
 */
trait ValueTrait
{

    /**
    * @return array
    */
    public function getValue()
    {
        return $this->data;
    }

    /**
    * @param array $value
    */
    public function setValue(array $value)
    {
        $this->data = $value;
    }
}
