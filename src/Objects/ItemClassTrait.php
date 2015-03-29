<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Trait for KeyValue factory.
 *
 * @internal
 */
trait ItemClassTrait
{
    /**
     * @var string
     */
    private static $defaultItemClass = 'andrefelipe\Orchestrate\Objects\KeyValue';

    /**
     * @var string
     */
    private static $minimumItemInterface = 'andrefelipe\Orchestrate\Objects\KeyValueInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's items (KeyValue).
     *
     * @return \ReflectionClass
     */
    public function getItemClass()
    {
        if (!isset($this->_itemClass)) {
            $this->_itemClass = new \ReflectionClass(self::$defaultItemClass);

            if (!$this->_itemClass->implementsInterface(self::$minimumItemInterface)) {
                throw new \RuntimeException('Item classes must implement ' . self::$minimumItemInterface);
            }
        }
        return $this->_itemClass;
    }

    /**
     * Set which class should be used to instantiate this list's items (KeyValue).
     *
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     */
    public function setItemClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_itemClass = $class;
        } else {
            $this->_itemClass = new \ReflectionClass($class);
        }

        if (!$this->_itemClass->implementsInterface(self::$minimumItemInterface)) {
            throw new \RuntimeException('Item classes must implement ' . self::$minimumItemInterface);
        }

        return $this;
    }
}
