<?php
namespace andrefelipe\Orchestrate\Common;

use JmesPath\Env as JmesPath;

/**
 * Implements \ArrayAccess, \Countable, ToJsonInterface and all of the
 * ObjectArray methods, except merge, which is left to each implementation.
 * 
 * @internal
 */
trait ObjectArrayTrait
{
    public function __get($key)
    {
        return isset($this->{$key}) ? $this->{$key} : null;
    }

    public function __set($key, $value)
    {
        if (is_array($value)) {
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
        if ($offset === null) {
            $offset = count($this);
        }
       
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
     * Helper method to merge instances.
     *
     * @param object $object
     * @param object $instance = null
     *
     * @return ObjectArray this
     */
    private function _mergeObject($object, $instance = null)
    {
        if (!is_object($instance)) {
            $instance = $this;
        }
        $index = count($instance);

        foreach (get_object_vars($object) as $key => $value) {

            if (is_int($key)) {
                $key = $index++;
            }
            $key = (string) $key;
    
            if (isset($instance->{$key}) && is_object($value) && is_object($instance->{$key})) {
                $this->_mergeObject($value, $instance->{$key});
            } else {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }

    public function toArray()
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            
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

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    public function extract($expression)
    {
        $result = JmesPath::search($expression, $this->toArray());
        return is_array($result) ? new ObjectArray($result) : $result;
    }
}
