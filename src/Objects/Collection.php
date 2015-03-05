<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;
// use andrefelipe\Orchestrate\Objects\Properties\ApplicationTrait;
// use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Collection extends AbstractList
{
    // use ApplicationTrait;
    // use CollectionTrait;

    public function __construct(Application $application, $name)
    {
        $this->setApplication($application);
        $this->setCollection($name);
    }
    


    public function item($key = null, $ref = null)
    {   
        // return $this->getChildClass()->newInstance(
        //     $this->getApplication(true),
        //     $this->getCollection(true),
        //     $key,
        //     $ref
        // );
        return $this->getChildClass()->newInstance()
            ->setApplication($this->getApplication(true))
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setRef($ref);
    }


}
