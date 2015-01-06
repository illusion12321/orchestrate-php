<?php
namespace andrefelipe\Orchestrate\Objects;

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





    public function __construct($collection, $key=null, $relation=null, $toCollection=null, $toKey=null)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->relation = $relation;
        $this->destinationCollection = $toCollection;
        $this->destinationKey = $toKey;
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
            // Orchestrate data export has timestamp, but the API doesn't return it yet
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
        }

        return $this;
    }







    // API


    /**
     * @return Relation self
     */
    public function put()
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noRelationException();
        $this->noDestinationCollectionException();
        $this->noDestinationKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$this->relation
            .'/'.$this->destinationCollection.'/'.$this->destinationKey;
        
        // request
        $this->request('PUT', $path);
        
        return $this;
    }

    
    /**
     * @return Relation self
     */
    public function delete()
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noRelationException();
        $this->noDestinationCollectionException();
        $this->noDestinationKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$this->relation
            .'/'.$this->destinationCollection.'/'.$this->destinationKey;

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

    protected function noDestinationCollectionException()
    {
        if (!$this->destinationCollection) {
            throw new \BadMethodCallException('There is no destination collection set yet. Please do so through setDestinationCollection() method.');
        }
    }

    protected function noDestinationKeyException()
    {
        if (!$this->destinationKey) {
            throw new \BadMethodCallException('There is no destination key set yet. Please do so through setDestinationKey() method.');
        }
    }



}