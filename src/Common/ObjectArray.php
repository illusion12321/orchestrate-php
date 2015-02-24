<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * 
 * Part of this code was appropriated from:
 * https://github.com/phalcon/cphalcon/blob/2.0.0/phalcon/config.zep
 * Which credit goes to Andres Gutierrez and Eduar Carvajal of the Phalcon team.
 */
class ObjectArray implements \ArrayAccess, \Countable, ToObjectInterface
{
    /**
     * @param array $values Values to set to the object on construct.
     */
    public function __construct(array $values = null)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $key = (string) $key;

                if (is_array($value)) {
                    $this->{$key} = new self($value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function __get($key)
    {
        return isset($this->{$key}) ? $this->{$key} : null;
    }

    public function __set($key, $value)
    {
        if (is_array($value)) {
            $this->{$key} = new self($value);
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
        return count(get_object_vars($this));
    }

    /**
     * Merges an object's values into the object.
     *
     * @param object $object
     * @return ObjectArray this 
     */
    public function merge($object)
    {
        return $this->_merge($object);
    }

    /**
     * Helper method to merge instances.
     *
     * @param object $object
     * @param object $instance = null
     *
     * @return ObjectArray this
     */
    private function _merge($object, $instance = null)
    {
        if (!is_object($instance)) {
            $instance = $this;
        }

        foreach (get_object_vars($object) as $key => $value) {
            
            if (isset($instance->{$key}) && is_object($value) && is_object($instance->{$key})) {
                $this->_merge($value, $instance->{$key});
            } else {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }

    public function toArray()
    {
        $result = [];

        foreach (get_object_vars($object) as $key => $value) {
            
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
        return new self($this->toArray());
    }

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }
}
