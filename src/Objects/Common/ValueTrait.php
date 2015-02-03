<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Value methods.
 * 
 * Requires that the target class also implements GuzzleHttp\HasDataTrait
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

        return $this;
    }
}
