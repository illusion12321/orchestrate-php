<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ToJsonInterface;
use JmesPath\Env as JmesPath;

abstract class AbstractObject extends AbstractResponse implements
\ArrayAccess,
ValueInterface,
ToJsonInterface
{
    /**
     * @var array Storage for user-defined properties mapped to getter/setters.
     */
    private $_propertyMap = [];

    /**
     * @param string $name The property name to map methods to.
     * @param boolean|string $getterName The getter method name. Method must exist in current object.
     *                                   Defaults to true, which will automatically try to find a
     *                                   method named after your property with camelCase, for example 'getName'.
     * @param boolean|string $setterName The setter method name. Method must exist in current object.
     *                                   Defaults to true, which will automatically try to find a
     *                                   method named after your property with camelCase, for example 'setName'.
     */
    protected function mapProperty($name, $getterName = true, $setterName = true)
    {
        if ($getterName === true || $setterName === true) {
            $capitalized = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));

            if ($getterName === true) {
                $getterName = 'get' . $capitalized;
            }
            if ($setterName === true) {
                $setterName = 'set' . $capitalized;
            }
        }

        if (!method_exists($this, $getterName)) {
            throw new \BadMethodCallException('A matching getter method could not be found, tried: ' . $getterName);
        }

        if (!method_exists($this, $setterName)) {
            throw new \BadMethodCallException('A matching setter method could not be found, tried: ' . $setterName);
        }

        $this->_propertyMap[$name] = [[$this, $getterName], [$this, $setterName]];
    }

    public function __get($key)
    {
        if (isset($this->_propertyMap[$key][0])) {
            return $this->_propertyMap[$key][0]();
        }
        return isset($this->{$key}) ? $this->{$key} : null;
    }

    public function __set($key, $value)
    {
        if (isset($this->_propertyMap[$key][1])) {
            $this->_propertyMap[$key][1]($value);
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
        if (is_null($offset) || is_numeric($offset)) {
            $this->noIndexedArrayException();
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

    public function getValue()
    {
        return (new ObjectArray($this->getMappedValues()))->merge($this);
    }

    public function toArray()
    {
        return array_merge($this->getMappedValues(), ObjectArray::objectToArray($this));
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

    public function extractValue($expression)
    {
        $valueArray = array_merge($this->getMappedValues(), ObjectArray::objectToArray($this));
        $result = JmesPath::search($expression, $valueArray);
        return is_array($result) ? new ObjectArray($result) : $result;
    }

    public function setValue(array $values)
    {
        if ($values) {
            foreach ($values as $key => $value) {

                if (is_numeric($key)) {
                    $this->noIndexedArrayException();
                }
                $this->{(string) $key} = $value;
            }
        }
        return $this;
    }

    public function mergeValue(ValueInterface $item)
    {
        ObjectArray::mergeObject($item->getValue(), $this);
        return $this;
    }

    public function resetValue()
    {
        foreach (ObjectArray::getPublicProperties($this) as $key) {
            $this->{$key} = null;
        }
        foreach ($this->_propertyMap as $key => $methods) {
            if (isset($methods[1])) {
                $methods[1](null);
            }
        }
        return $this;
    }

    private function noIndexedArrayException()
    {
        throw new \RuntimeException('Indexed arrays not allowed at the root of ' . get_class($this) . ' objects.');
    }

    /**
     * Helper to get the mapped properties to getters.
     *
     * @return array
     */
    private function getMappedValues()
    {
        $result = [];
        foreach ($this->_propertyMap as $key => $methods) {
            if (isset($methods[0])) {
                $result[$key] = $methods[0]();
            }
        }
        return $result;
    }
}
