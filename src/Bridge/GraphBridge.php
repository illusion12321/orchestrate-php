<?php
namespace andrefelipe\Orchestrate\Bridge;

use andrefelipe\Orchestrate\Objects\AbstractObject;
use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\Relation;
use andrefelipe\Orchestrate\Objects\Relations;


class GraphBridge
{
    private $parent;
    

    public function __construct(AbstractObject $parent)
    {
        $this->parent = $parent;
    }

    

    /**
     * @param string $relation
     * @param string $toCollection
     * @param string $toKey
     * @return Relation
     */
    public function put($relation, $toCollection, $toKey)
    {
        return (new Relation($this->parent->getCollection(), $this->parent->getKey(), $relation))
            ->setApplication($this->parent->getApplication())
            ->put($toCollection, $toKey);
    }

    /**
     * @param string $relation
     * @param string $toCollection
     * @param string $toKey
     * @return Relation
     */
    public function delete($relation, $toCollection, $toKey)
    {
        return (new Relation($this->parent->getCollection(), $this->parent->getKey(), $relation))
            ->setApplication($this->parent->getApplication())
            ->delete($toCollection, $toKey);
    }


    /**
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     * @return Relations
     */
    public function getList($kind, $limit=10, $offset=0)
    {
       return (new Relations($this->parent->getCollection(), $this->parent->getKey()))
           ->setApplication($this->parent->getApplication())
           ->listRelations($kind, $limit, $offset);
    }


    



}