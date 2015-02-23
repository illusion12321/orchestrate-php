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
     * Get item Value.
     * 
     * @return array
     */
    public function getValue()
    {
        return (new ObjectArray())->merge($this);
    }

    /**
     * Set item Value. Will reset the values before.
     * 
     * @param array $values
     */
    public function setValue(array $values)
    {
        $this->resetValue();

        if ($values) {
            foreach ($values as $key => $value) {
                $key = (string) $key;
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Set all public properties to null.
     * 
     * @param array $value
     */
    public function resetValue()
    {
        $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $this->{$property->name} = null;
        }

        return $this;
    }
}
