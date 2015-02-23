<?php
namespace andrefelipe\Orchestrate\Common;

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
        return (new ObjectArray())->merge($this);
    }

    /**
    * @param array $value
    */
    public function setValue(array $data)
    {
        // TODO should reset the object!!
        if ($data) {
            foreach ($data as $key => $value) {
                $key = (string) $key;
                $this->{$key} = $value;
            }
        }

        return $this;
    }
}
