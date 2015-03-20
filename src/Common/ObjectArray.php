<?php
namespace andrefelipe\Orchestrate\Common;

use JmesPath\Env as JmesPath;

/**
 *
 * This class was inspired by:
 * https://github.com/phalcon/cphalcon/blob/2.0.0/phalcon/config.zep
 * Which credit goes to Andres Gutierrez and Eduar Carvajal of the
 * wonderful Phalcon project.
 */
class ObjectArray implements \ArrayAccess, \Countable, ToJsonInterface
{
    /**
     * @param array $values Values to set to the object on construct.
     */
    public function __construct(array $values = null)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
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

    public function toArray()
    {
        return self::objectToArray($this);
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

    /**
     * Merges an array's or object's values into the object.
     *
     * @param array|object $object
     * @return ObjectArray this
     */
    public function merge($object)
    {
        if (is_object($object)) {

            self::mergeValues($object, $this);

        } else if (is_array($object)) {

            $index = count($this);

            foreach ($object as $key => $value) {

                if (is_int($key)) {
                    $key = $index++;
                }
                $key = (string) $key;

                if (isset($this->{$key}) && is_object($value) && is_object($this->{$key})) {
                    self::mergeValues($value, $this->{$key});
                } else {
                    $this->{$key} = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Helper method to merge instance's public properties.
     *
     * @param object $source
     * @param object $target
     */
    private static function mergeValues($source, $target)
    {
        $index = count($target);

        foreach (get_object_vars($source) as $key => $value) {

            if (is_int($key)) {
                $key = $index++;
            }
            $key = (string) $key;

            if (isset($target->{$key}) && is_object($value) && is_object($target->{$key})) {
                self::mergeValues($value, $target->{$key});
            } else {
                $target->{$key} = $value;
            }
        }
    }

    /**
     * Gets an object public properties out, into an Array.
     * If any value is an object, and has a toArray method, it will be executed.
     *
     * @param object $object
     * @return array
     */
    public static function objectToArray($object)
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

    /**
     * Helper to get an object public properties. Optionally include the values too.
     *
     * This method uses 'get_object_vars' and the reason is that if you use
     * get_object_vars inside your class, it will get all currently accessible properties,
     * i.e. your private and protected vars. A work around that is to use:
     * $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
     * but that is a considerable overhead, around 50% slower, at least on PHP 5.5 where I checked.
     *
     * @param object $object
     * @param boolean $includeValues Optionally include the values too. Defaults to false.
     * @return array
     */
    public static function getPublicProperties($object, $includeValues = false)
    {
        return $includeValues ? get_object_vars($object) : array_keys(get_object_vars($object));
    }
}
