<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the KeyValue class reflector.
 * 
 * @internal
 */
trait KeyValueReflectionTrait
{
    /**
     * @var string
     */
    protected static $defaultKeyValueClass = '\andrefelipe\Orchestrate\Objects\KeyValue';

    /**
     * @var string
     */
    protected static $minimumKeyValueInterface = '\andrefelipe\Orchestrate\Objects\KeyValueInterface';

    /**
     * @var \ReflectionClass
     */
    private $_keyValueClass;    
    
    /**
     * Get the ReflectionClass that is being used to instantiate this list's KeyValue instances.
     * 
     * @return \ReflectionClass
     */
    public function getKeyValueClass()
    {
        if (!isset($this->_keyValueClass)) {
            $this->_keyValueClass = new \ReflectionClass(static::$defaultKeyValueClass);

            if (!$this->_keyValueClass->implementsInterface(static::$minimumKeyValueInterface)) {
                throw new \RuntimeException('Child classes must implement '.static::$minimumKeyValueInterface);
            }
        }
        return $this->_keyValueClass;
    }

    /**
     * Set which class should be used to instantiate this list's KeyValue instances.
     * 
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     * 
     * @return AbstractClient self
     */
    public function setKeyValueClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_keyValueClass = $class;
        } else {
            $this->_keyValueClass = new \ReflectionClass($class);
        }
        
        if (!$this->_keyValueClass->implementsInterface(static::$minimumKeyValueInterface)) {
            throw new \RuntimeException('Child classes must implement '.static::$minimumKeyValueInterface);
        }

        return $this;
    }
}