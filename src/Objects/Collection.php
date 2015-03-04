<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\ApplicationTrait;
use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Collection
{
    use ApplicationTrait;
    use CollectionTrait;

    public function __construct($collection)
    {
        $this->setCollection($collection);
    }

    private $childClass;
    private $childEventClass;

    public function setChildClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->childClass = $class;
        } else {
            $this->childClass = new \ReflectionClass($class);
        }
    }

    public function getChildClass()
    {
        if (!isset($this->childClass)) {
            $this->childClass = new \ReflectionClass('\andrefelipe\Orchestrate\Objects\KeyValue');
        }

        return $this->childClass;
    }

    public function setChildEventClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->childEventClass = $class;
        } else {
            $this->childEventClass = new \ReflectionClass($class);
        }
    }

    public function getChildEventClass()
    {
        if (!isset($this->childEventClass)) {
            $this->childEventClass = new \ReflectionClass('\andrefelipe\Orchestrate\Objects\Event');
        }

        return $this->childEventClass;
    }


    public function item($keyValue = null, $autoload = false)
    {
        if (is_string($keyValue)) {
            $keyValue = ['key' => $keyValue];
        }
        
        $item = $this->getChildClass()->newInstance()
            ->setApplication($this->getApplication(true))
            ->setCollection($this->getCollection(true))
            ->init($keyValue);            

        if ($autoload && $item->getKey()) {
            $item->get($item->getRef());
        }
        return $item;
    }
}
