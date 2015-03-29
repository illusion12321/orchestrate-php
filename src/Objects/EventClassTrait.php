<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Trait for Event factory.
 *
 * @internal
 */
trait EventClassTrait
{

    /**
     * @var string
     */
    private static $defaultEventClass = '\andrefelipe\Orchestrate\Objects\Event';

    /**
     * @var string
     */
    private static $minimumEventInterface = '\andrefelipe\Orchestrate\Objects\EventInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's events.
     *
     * @return \ReflectionClass
     */
    public function getEventClass()
    {
        if (!isset($this->_eventClass)) {
            $this->_eventClass = new \ReflectionClass(self::$defaultEventClass);

            if (!$this->_eventClass->implementsInterface(self::$minimumEventInterface)) {
                throw new \RuntimeException('Event classes must implement ' . self::$minimumEventInterface);
            }
        }
        return $this->_eventClass;
    }

    /**
     * Set which class should be used to instantiate this list's events.
     *
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     */
    public function setEventClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_eventClass = $class;
        } else {
            $this->_eventClass = new \ReflectionClass($class);
        }

        if (!$this->_eventClass->implementsInterface(self::$minimumEventInterface)) {
            throw new \RuntimeException('Event classes must implement ' . self::$minimumEventInterface);
        }

        return $this;
    }
}
