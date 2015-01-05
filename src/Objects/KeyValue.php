<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\AbstractObject;
use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\RefTrait;
use andrefelipe\Orchestrate\Objects\Common\TombstoneTrait;
use andrefelipe\Orchestrate\Bridge\GraphBridge;


class KeyValue extends AbstractObject
{
    use KeyTrait;
    use RefTrait;
    use TombstoneTrait;


    /**
     * @var int
     */
    protected $refTime = 0;

    /**
     * @var float
     */
    protected $score = 0;

    


    public function __construct($collection, $key=null)
    {
        parent::__construct($collection);
        $this->key = $key;
    }


    protected $graph = null;

    public function graph()
    {
        if (!$this->graph) {
            $this->graph = new GraphBridge($this);
        }

        return $this->graph;
    }


    /**
     * @return int
     */
    public function getRefTime()
    {
        return $this->refTime;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->data;
    }

    /**
     * @param array $value
     */
    public function setValue(array $value)
    {
        $this->data = $value;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'item',
            'path' => [
                'collection' => $this->collection,
                'key' => $this->key,
                'ref' => $this->ref,
            ],
            'value' => $this->data,
        ];

        if ($this->refTime)
            $result['path']['reftime'] = $this->refTime;

        if ($this->score)
            $result['path']['score'] = $this->score;

        if ($this->tombstone)
            $result['path']['tombstone'] = $this->tombstone;
        
        return $result;
    }



    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->ref = null;
        $this->refTime = 0;
        $this->score = 0;
        $this->tombstone = false;
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
            
            if ($key === 'collection')
                $this->collection = $value;

            if ($key === 'key')
                $this->key = $value;

            if ($key === 'ref')
                $this->ref = $value;

            if ($key === 'reftime')
                $this->refTime = (int) $value;

            if ($key === 'score')
                $this->score = (float) $value;

            if ($key === 'tombstone')
                $this->tombstone = (boolean) $value;

            if ($key === 'value')
                $this->data = (array) $value;
        }

        return $this;
    }







    // API



    /**
     * @return KeyValue self
     */
    public function get($ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key;

        if ($ref) {
            $path .= '/refs/'.trim($ref, '"');
        }

        // request
        $this->request('GET', $path);

        // set values
        if ($this->isSuccess()) {
            $this->data = $this->body;
            $this->setRefFromETag();
        }
        else {
            $this->data = [];
        }

        return $this;
    }

    
    
    /**
     * @return KeyValue self
     */
    public function put(array $value=null, $ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        if ($value === null) {
            $value = $this->data;
        }

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];

        } elseif ($ref === false) {

            // set If-None-Match
            $options['headers'] = ['If-None-Match' => '"*"'];

        }

        // request
        $this->request('PUT', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();
        }

        // set value as input value, even if not success, so we can retry
        $this->data = $value;


        return $this;
    }



    /**
     * @return KeyValue self
     */
    public function post(array $value=null)
    {
        // required values
        $this->noCollectionException();

        if ($value === null) {
            $value = $this->data;
        }

        // request
        $this->request('POST', $this->collection, ['json' => $value]);
        
        // set values
        if ($this->isSuccess()) {
            $this->key = null;
            $this->ref = null;
            $this->setKeyRefFromLocation();
        }

        // set value as input value, even if not success, so we can retry
        $this->data = $value;

        return $this;
    }




    /**
     * @return KeyValue self
     */
    public function delete($ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = [];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        return $this;
    }




    /**
     * @return KeyValue self
     */
    public function purge()
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);
        
        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->ref = null;
        }

        return $this;
    }


    // Graph

    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @return KeyValue self
     */
    public function putRelation($kind, $toCollection, $toKey)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;
        
        // request
        $this->request('PUT', $path);
        
        return $this;
    }

    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @return KeyValue self
     */
    public function deleteRelation($kind, $toCollection, $toKey)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;

        // request
        $this->request('DELETE', $path, ['query' => ['purge' => 'true']]);
        
        return $this;
    }










    // Cross-object API
    // TODO still consider to remove these, it's confusing to sometimes return self, other times, completely different values
    // I got myself sometimes read the success in the current KeyValue object

    /**
     * @return Relations
     */
    public function listRelations($kind, $limit=10, $offset=0)
    {
        return (new Relations($this->collection, $this->key))
            ->setApplication($this->getApplication())
            ->listRelations($kind, $limit, $offset);
    }

    /**
     * @return Refs
     */
    public function listRefs($limit=10, $offset=0, $values=false)
    {
        return (new Refs($this->collection, $this->key))
            ->setApplication($this->getApplication())
            ->listRefs($limit, $offset, $values);
    }


    /**
     * @return Events
     */
    public function listEvents($type, $limit=10, array $range=null)
    {
        return (new Events($this->collection, $this->key, $type))
            ->setApplication($this->getApplication())
            ->listEvents($limit, $range);
    }







    // helpers

    
    protected function setKeyRefFromLocation()
    {
        // Location: /v0/collection/key/refs/ad39c0f8f807bf40

        $location = $this->response->getHeader('Location');
        if (!$location)
            $location = $this->response->getHeader('Content-Location');

        $location = explode('/', trim($location, '/'));
        if (count($location) > 4)
        {
            $this->key = $location[2];
            $this->ref = $location[4];
        }
    }









    // override ArrayAccess

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || is_int($offset)) {
           throw new \RuntimeException('Sorry, indexed arrays not allowed at the root of KeyValue objects.');
        } else {
            $this->data[$offset] = $value;
        }
    }






}