<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\RefTrait;
use andrefelipe\Orchestrate\Objects\Properties\ReftimeTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractObject
{
    use CollectionTrait;
    use KeyTrait;
    use RefTrait;
    use ReftimeTrait;

    /**
     * @var float
     */
    private $_score = null;

    /**
     * @var float
     */
    private $_distance = null;

    /**
     * @var boolean
     */
    private $_tombstone = false;    

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     */
    public function __construct($collection = null, $key = null, $ref = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setRef($ref);
    }

    public function refs()
    {
        return (new Refs($this->getCollection(true), $this->getKey(true)))
            ->setClient($this->getClient(true))
            ->setChildClass(new \ReflectionClass($this));
    }

    public function events($type)
    {
        return (new Events($this->getCollection(true), $this->getKey(true), $type))
            ->setClient($this->getClient(true));
    }

    public function event($type, $timestamp = null, $ordinal = null)
    {
        return (new Event($this->getCollection(true), $this->getKey(true), $type, $timestamp, $ordinal))
            ->setClient($this->getClient(true));
    }

    // private $graph;
    // public function relations()
    // {
    //     if (!$graph) {
    //         $graph = (new Graph())
    //             ->setClient($this->getClient(true))
    //             ->setCollection($this->getCollection(true))
    //             ->setKey($this->getKey(true));
    //     }
    //     return $graph;
    // }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->_score;
    }    

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->_distance;
    }

    /**
     * @return boolean
     */
    public function isTombstone()
    {
        return $this->_tombstone;
    }

    

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_ref = null;
        $this->_score = null;
        $this->_distance = null;
        $this->_reftime = null;
        $this->_tombstone = false;
        $this->resetValue();
    }

    public function init(array $values)
    {        
        if (empty($values)) {
            return;
        }

        if (!empty($values['path'])) {
            $values = array_merge($values, $values['path']);
        }

        foreach ($values as $key => $value) {
            
            if ($key === 'collection')
                $this->setCollection($value);

            elseif ($key === 'key')
                $this->setKey($value);

            elseif ($key === 'ref')
                $this->setRef($value);

            elseif ($key === 'value')
                $this->setValue((array) $value);

            elseif ($key === 'score')
                $this->_score = (float) $value;

            elseif ($key === 'distance')
                $this->_distance = (float) $value;

            elseif ($key === 'reftime')
                $this->_reftime = (int) $value;

            elseif ($key === 'tombstone')
                $this->_tombstone = (boolean) $value;
        }

        return $this;
    }

    public function toArray()
    {
        $result = [
            'kind' => 'item',
            'path' => [
                'collection' => $this->getCollection(),
                'key' => $this->getKey(),
                'ref' => $this->getRef(),
            ],
            'value' => parent::toArray(),
        ];

        if ($this->_score !== null)
            $result['score'] = $this->_score;

        if ($this->_distance !== null)
            $result['distance'] = $this->_distance;

        if ($this->_reftime !== null)
            $result['path']['reftime'] = $this->_reftime;

        if ($this->_tombstone)
            $result['path']['tombstone'] = $this->_tombstone;
        
        return $result;
    }

    /**
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);

        if ($ref) {
            $path .= '/refs/'.trim($ref, '"');
        }

        // request
        $this->request('GET', $path);

        // set values
        $this->resetValue();
        $this->_ref = null;

        if ($this->isSuccess()) {
            $this->setValue($this->getBody());
            $this->setRefFromETag();
        }

        return $this->isSuccess();
    }    
    
    /**
     * @param array $value
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
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

            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param array $value
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // request
        $this->request('POST', $this->getCollection(true), ['json' => $newValue]);
        
        // set values
        if ($this->isSuccess()) {
            $this->_key = null;
            $this->_ref = null;
            $this->setKeyRefFromLocation();
            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $operations->toArray()];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }
        }
        
        return $this->isSuccess();
    }

    /**
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(array $value, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = [];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        return $this->isSuccess();
    }

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);
        
        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->_ref = null;
        }

        return $this->isSuccess();
    }

    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelation($kind, $toCollection, $toKey)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;
        
        // request
        $this->request('PUT', $path);
        
        return $this->isSuccess();
    }
    
    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelation($kind, $toCollection, $toKey)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;

        // request
        $this->request('DELETE', $path, ['query' => ['purge' => 'true']]);
        
        return $this->isSuccess();
    }
    
    /**
     * Helper to set the Key and Ref from a Orchestrate Location HTTP header.
     * For example: Location: /v0/collection/key/refs/ad39c0f8f807bf40
     */
    private function setKeyRefFromLocation()
    {
        $location = $this->getResponse()->getHeader('Location');
        if (!$location) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }

        $location = explode('/', trim($location, '/'));
        if (count($location) > 4)
        {
            $this->setKey($location[2]);
            $this->setRef($location[4]);
        }
    }
}
