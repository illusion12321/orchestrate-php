<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Event class reflector.
 * 
 * @internal
 */
trait EventReflectionTrait
{
    /**
     * @var \ReflectionClass
     */
    private $_eventClass;

    /**
     * @var string
     */
    protected static $defaultEventClass = '\andrefelipe\Orchestrate\Objects\Event';

    /**
     * @var string
     */
    protected static $minimumEventInterface = '\andrefelipe\Orchestrate\Objects\EventInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's events instances.
     * 
     * @return \ReflectionClass
     */
    public function getEventClass()
    {
        if (!isset($this->_eventClass)) {
            $this->_eventClass = new \ReflectionClass(static::$defaultEventClass);

            if (!$this->_eventClass->implementsInterface(static::$minimumEventInterface)) {
                throw new \RuntimeException('Child classes must implement '.static::$minimumEventInterface);
            }
        }
        return $this->_eventClass;
    }

    /**
     * Set which class should be used to instantiate this list's events instances.
     * 
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     * 
     * @return AbstractClient self
     */
    public function setEventClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_eventClass = $class;
        } else {
            $this->_eventClass = new \ReflectionClass($class);
        }
       
        if (!$this->_eventClass->implementsInterface(static::$minimumEventInterface)) {
            throw new \RuntimeException('Child classes must implement '.static::$minimumEventInterface);
        }

        return $this;
    }
}