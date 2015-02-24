<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\CollectionTrait;
use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ToObjectInterface;

abstract class AbstractObject extends AbstractResponse implements
    \ArrayAccess,
    \Countable,
    ToObjectInterface
{
    use CollectionTrait;
    
    /**
     * @param string $collection
     */
    public function __construct($collection)
    {
        $this->setCollection($collection);
    }

    public function __get($key)
    {
        if ($getter = $this->getMethod($key)) {
            return $this->$getter();
        }
        return isset($this->{$key}) ? $this->{$key} : null;
    }    

    public function __set($key, $value)
    {
        if ($setter = $this->setMethod($key)) {
            $this->$setter($value);
        } else if (is_array($value)) {
            $this->{$key} = new ObjectArray($value);
        } else {
            $this->{$key} = $value;
        }
    }

    public function __unset($key)
    {
        return $this->{$key} = null;
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{(string) $offset} = $value;
    }

    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function count()
    {
        $properties = (new \ReflectionObject($this))
            ->getProperties(\ReflectionProperty::IS_PUBLIC);

        return count($properties);
    }

    public function toArray()
    {
        $result = [];
        $properties = (new \ReflectionObject($this))
            ->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $key = $property->name;
            $value = $this->{$key};

            if ($value === null) {
                continue;
            }

            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $result[$key] = $value->toArray();
                } else {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function toObject()
    {
        return new ObjectArray($this->toArray());
    }

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    private function getMethod($name)
    {
        $name = 'get'.ucfirst($name);
        return method_exists($this, $name) ? $name : false;
    }

    private function setMethod($name)
    {
        $name = 'set'.ucfirst($name);
        return method_exists($this, $name) ? $name : false;
    }
}
