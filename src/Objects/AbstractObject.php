<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ObjectArrayTrait;
use andrefelipe\Orchestrate\Common\ToJsonInterface;

abstract class AbstractObject extends AbstractResponse implements
    \ArrayAccess,
    \Countable,
    ValueInterface,
    ToJsonInterface
{
    use ObjectArrayTrait;

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || (int) $offset === $offset) {
           throw new \RuntimeException('Sorry, indexed arrays not allowed at the root of '.get_class($this).' objects.');
        }

        $this->{(string) $offset} = $value;
    }

    public function getValue()
    {
        return (new ObjectArray())->merge($this);
    }

    public function setValue(array $values)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
            }
        }
        return $this;
    }

    public function mergeValue(ValueInterface $item)
    {
        if ($item) {
            $this->_mergeObject($item);
        }
    }

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
