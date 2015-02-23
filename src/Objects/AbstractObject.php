<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\CollectionTrait;
use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ObjectArrayTrait;
use andrefelipe\Orchestrate\Common\ToObjectInterface;

abstract class AbstractObject extends AbstractResponse implements
    \ArrayAccess,
    \Countable,
    ToObjectInterface
{    
    use CollectionTrait;
    use ObjectArrayTrait;
    
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

    public function offsetGet($offset)
    {
        if ($getter = $this->getMethod($offset)) {
            return $this->$getter();
        }
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    public function offsetSet($offset, $value)
    {
        $offset = (string) $offset;

        if ($setter = $this->setMethod($offset)) {
            $this->$setter($value);
        } else if (is_array($value)) {
            $this->{$offset} = new ObjectArray($value);
        } else {
            $this->{$offset} = $value;
        }
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
