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
     * Set item Value.
     * 
     * @param array $values
     */
    public function setValue(array $values)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
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
        // strictly gets the public properties, otherwise we would be getting
        // all properties accessible on this scope (i.e. protected and privates)

        foreach ($properties as $property) {
            $this->{$property->name} = null;
        }        
        return $this;
    }
}
