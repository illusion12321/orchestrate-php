<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait that implements the ObjectArray methods as well as
 * \ArrayAccess, \Countable and ToObjectInterface
 * 
 * @internal
 */
trait ObjectArrayTrait
{
    /**
     * Gets an attribute using the array-syntax. Will return null if the value is not set.
     *
     *<code>
     * print_r($item['name']);
     *</code>
     */
    public function offsetGet($offset)
    {
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
     * Sets an attribute using the array-syntax.
     *
     *<code>
     * $item['address'] = ['street' => 'Street Name'];
     *</code>
     */
    public function offsetSet($offset, $value)
    {
        $offset = (string) $offset;

        if (is_array($value)) {
            $this->{$offset} = new self($value);
        } else {
            $this->{$offset} = $value;
        }
    }

    /**
     * Unsets an attribute using the array-syntax.
     *
     *<code>
     * unset($item['name']);
     *</code>
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
        // TODO test this, could need to be set to null, instead of unset
    }

    /**
     * Allows to check whether an attribute is defined using the array-syntax.
     *
     *<code>
     * var_dump(isset($item['name']));
     *</code>
     * 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Returns the count of properties set in the config
     *
     *<code>
     * print count($config);
     *</code>
     *
     * or
     *
     *<code>
     * print $config->count();
     *</code>
     */
    public function count()
    {
        return count(get_object_vars($this));
    }

    /**
     * Merges an object's values into the object.
     *
     * @param object $object
     * @return this merged object
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
     * @return ObjectArray merged object
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

        foreach (get_object_vars($this) as $key => $value) {
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
