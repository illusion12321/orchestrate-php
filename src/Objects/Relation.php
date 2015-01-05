<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\AbstractObject;
use andrefelipe\Orchestrate\Objects\Common\KeyTrait;

class Relation extends AbstractObject
{
    use KeyTrait;



    /**
     * @var string
     */
    protected $relation = null;

    /**
     * @var string
     */
    protected $destinationCollection = null;

    /**
     * @var string
     */
    protected $destinationKey = null;




    public function __construct($collection, $key=null, $relation=null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->relation = $relation;
    }


    /**
     * @return string
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationCollection()
    {
        return $this->destinationCollection;
    }

    /**
     * @param string $collection
     */
    public function setDestinationCollection($collection)
    {
        $this->destinationCollection = $collection;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationKey()
    {
        return $this->destinationKey;
    }

    /**
     * @param string $key
     */
    public function setDestinationKey($key)
    {
        $this->destinationKey = $key;

        return $this;
    }

    
    
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'relationship',
            'source' => [
                'collection' => $this->collection,
                'key' => $this->key,
            ],
            'destination' => [
                'collection' => $this->destinationCollection,
                'key' => $this->destinationKey,
            ],
            'relation' => $this->relation,
            // 'timestamp' => $this->timestamp,
        ];

        return $result;
    }



    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->relation = null;
        $this->destinationCollection = null;
        $this->destinationKey = null;
        // $this->timestamp = 0;
        $this->data = [];
    }



    public function init(array $values)
    {
        $this->reset();
        
        if (empty($values))
            return;

        if (!empty($values['path'])) {
            $values = array_merge($values, $values['path']);
        }

        foreach ($values as $key => $value) {
            
            if ($key === 'source') {
                $this->collection = $value['collection'];
                $this->key = $value['key'];
            }

            if ($key === 'destination') {
                $this->destinationCollection = $value['collection'];
                $this->destinationKey = $value['key'];
            }

            if ($key === 'relation')
                $this->relation = $value;

            // if ($key === 'timestamp')
            //     $this->timestamp = (int) $value;
        }

        return $this;
    }







    // API


    /**
     * @param string $toCollection
     * @param string $toKey
     * @return Relation self
     */
    public function put($toCollection, $toKey)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noRelationException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$this->relation.'/'.$toCollection.'/'.$toKey;
        
        // request
        $this->request('PUT', $path);
        
        return $this;
    }
    
    
    /**
     * @param string $toCollection
     * @param string $toKey
     * @return Relation self
     */
    public function delete($toCollection, $toKey)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noRelationException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$this->relation.'/'.$toCollection.'/'.$toKey;

        // request
        $this->request('DELETE', $path, ['query' => ['purge' => 'true']]);
        
        return $this;
    }



    protected function noRelationException()
    {
        if (!$this->relation) {
            throw new \BadMethodCallException('There is no relation set yet. Please do so through setRelation() method.');
        }
    }



}