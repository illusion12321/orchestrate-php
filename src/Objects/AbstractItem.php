<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ToJsonInterface;
use andrefelipe\Orchestrate\Common\ToJsonTrait;
use JmesPath\Env as JmesPath;

abstract class AbstractItem extends AbstractResponse implements
\ArrayAccess,
\Serializable,
ValueInterface,
ToJsonInterface
{
    use ToJsonTrait;

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
        $this->_propertyMap[$name] = [];

        if ($getterName === true || $setterName === true) {
            $capitalized = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));

            if ($getterName === true) {
                $getterName = 'get'.$capitalized;
            }
            if ($setterName === true) {
                $setterName = 'set'.$capitalized;
            }
        }

        if ($getterName) {
            if (method_exists($this, $getterName)) {
                $this->_propertyMap[$name][0] = [$this, $getterName];
            } else {
                throw new \BadMethodCallException('A matching getter method could not be found, tried: '.$getterName);
            }
        }

        if ($setterName) {
            if (method_exists($this, $setterName)) {
                $this->_propertyMap[$name][1] = [$this, $setterName];
            } else {
                throw new \BadMethodCallException('A matching setter method could not be found, tried: '.$setterName);
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_propertyMap[$name])) {
            if (isset($this->_propertyMap[$name][0])) {
                return $this->_propertyMap[$name][0]();
            } else {
                return null;
            }
        }
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (isset($this->_propertyMap[$name])) {
            if (isset($this->_propertyMap[$name][1])) {
                $this->_propertyMap[$name][1]($value);
            }
        } else {
            $this->{$name} = $value;
        }
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->{$name} = null;
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || is_numeric($offset)) {
            $this->noIndexedArrayException();
        }

        $this->{(string) $offset} = $value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }

    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * @return ObjectArray
     */
    public function getValue()
    {
        return (new ObjectArray($this->getMappedValues()))->merge($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getMappedValues(true), ObjectArray::objectToArray($this));
    }

    /**
     * @param string $expression
     * @return ObjectArray|mixed|null
     */
    public function extract($expression)
    {
        $result = JmesPath::search($expression, $this->toArray());
        return is_array($result) ? new ObjectArray($result) : $result;
    }

    /**
     * @param string $expression
     * @return ObjectArray|mixed|null
     */
    public function extractValue($expression)
    {
        $valueArray = array_merge($this->getMappedValues(), ObjectArray::objectToArray($this));
        $result = JmesPath::search($expression, $valueArray);
        return is_array($result) ? new ObjectArray($result) : $result;
    }

    /**
     * @param array $values
     * @return AbstractItem
     */
    public function setValue(array $values)
    {
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
            }
        }
        return $this;
    }

    /**
     * @param ValueInterface $item
     * @return AbstractItem
     */
    public function mergeValue(ValueInterface $item)
    {
        ObjectArray::mergeObject($item->getValue(), $this);
        return $this;
    }

    /**
     * @return AbstractItem
     */
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

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * @param string $serialized
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function unserialize($serialized)
    {
        if (is_string($serialized)) {
            $data = unserialize($serialized);

            if (is_array($data)) {

                $this->init($data);
                return;
            }
        }
        throw new \InvalidArgumentException('Invalid serialized data type.');
    }

    private function noIndexedArrayException()
    {
        throw new \RuntimeException('Indexed arrays not allowed at the root of '.get_class($this).' objects.');
    }

    /**
     * Helper to get the mapped properties to getters.
     *
     * @return array
     */
    private function getMappedValues($skipNull = false)
    {
        $result = [];
        foreach ($this->_propertyMap as $key => $methods) {
            if (isset($methods[0])) {

                $value = $methods[0]();

                if (!$skipNull || $value !== null) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}
