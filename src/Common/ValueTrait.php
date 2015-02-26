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
     * @var array
     */
    private $_value = [];
    
    /**
     * Get item Value.
     * 
     * @return array
     */
    public function getValue()
    {
        return (new ObjectArray())->merge($this)->merge($this->_value);
    }

    /**
     * Set item Value.
     * 
     * @param array $values
     */
    public function setValue(array $values)
    {
        $reserved = $this->getReservedProperties();

        if ($values) {
            foreach ($values as $key => $value) {
                $key = (string) $key;

                if (isset($reserved[$key])) {
                    $this->_value[$key] = $value;
                } else {
                    $this->{$key} = $value;
                }
            }
        };

        return $this;
    }

    /**
     * Set all public properties to null.
     * 
     * @param array $value
     */
    public function resetValue()
    {
        // está aqui... o key agora é pubic então ele está resetandp
        // poderia fazer um protection com o getReserved
        // mas acho que tem meios mais lógicos de fazer isso

        $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $this->{$property->name} = null;
        }

        $this->_value = [];

        return $this;
    }
}
