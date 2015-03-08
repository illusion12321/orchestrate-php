<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;
use andrefelipe\Orchestrate\Objects\Properties\TimestampTrait;
use andrefelipe\Orchestrate\Objects\Properties\OrdinalTrait;
use andrefelipe\Orchestrate\Objects\Properties\RefTrait;
use andrefelipe\Orchestrate\Objects\Properties\ReftimeTrait;

class Event extends AbstractObject
{
    use CollectionTrait;
    use KeyTrait;
    use TypeTrait;
    use TimestampTrait;
    use OrdinalTrait;
    use RefTrait;
    use ReftimeTrait;

    /**
    * @var string
    */
    private $_ordinalStr = null;


    public function __construct($collection = null, $key = null, $type = null, $timestamp = null, $ordinal = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
        $this->setTimestamp($timestamp);
        $this->setOrdinal($ordinal);
    }

    /**
      * @return string
      */
     public function getOrdinalStr()
     {
         return $this->_ordinalStr;
     }
    
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'event',
            'path' => [
                'collection' => $this->getCollection(),
                'kind' => 'event',
                'key' => $this->getKey(),
                'type' => $this->getType(),
                'timestamp' => $this->getTimestamp(),
                'ordinal' => $this->getOrdinal(),
                'ref' => $this->getRef(),
                'reftime' => $this->getReftime(),
                'ordinal_str' => $this->getOrdinalStr(),
            ],
            'value' => parent::toArray(),
            'timestamp' => $this->getTimestamp(),
            'ordinal' => $this->getOrdinal(),
            'reftime' => $this->getReftime(),
        ];

        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_type = null;
        $this->_timestamp = null;
        $this->_ordinal = null;
        $this->_ref = null;
        $this->_reftime = null;
        $this->_ordinalStr = null;
        $this->resetValue();
    }

    public function init(array $values)
    {
        if (empty($values))
            return;

        if (!empty($values['path'])) {
            $values = array_merge($values, $values['path']);
        }

        foreach ($values as $key => $value) {
            
            if ($key === 'collection')
                $this->setCollection($value);

            elseif ($key === 'key')
                $this->setKey($value);            

            elseif ($key === 'type')
                $this->setType($value);

            elseif ($key === 'timestamp')
                $this->setTimestamp($value);

            elseif ($key === 'ordinal')
                $this->setOrdinal($value);

            elseif ($key === 'ref')
                $this->setRef($value);

            elseif ($key === 'reftime')
                $this->_reftime = (int) $value;

            elseif ($key === 'ordinal_str')
                $this->_ordinalStr = $value;

            elseif ($key === 'value')
                $this->setValue((array) $value);
        }

        return $this;
    }

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function get()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
            .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);
     
        // request
        $this->request('GET', $path);

        // set values
        $this->resetValue();
        $this->_ref = null;
        $this->_reftime = null;
        $this->_ordinalStr = null;

        if ($this->isSuccess()) {
            $this->init($this->getBody());
        }

        return $this->isSuccess();
    }
    
    /**
     * @param array $value
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
            .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);
        
        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PUT', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->_reftime = null;
            $this->setRefFromETag();
            $this->setTimestampAndOrdinalFromLocation();

            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param array $value
     * @param int $timestamp
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function post(array $value = null, $timestamp = null)
    {        
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/events/'.$this->getType(true);

        if ($timestamp === true) {
            $timestamp = $this->getTimestamp();
        }        
        if ($timestamp) {
            $path .= '/'.$timestamp;
        }

        $newValue = $value === null ? parent::toArray() : $value;

        // request
        $this->request('POST', $path, ['json' => $newValue]);
        
        // set values
        if ($this->isSuccess()) {
            $this->_timestamp = null;
            $this->_ordinal = null;
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->setRefFromETag();
            $this->setTimestampAndOrdinalFromLocation();
            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
            .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

        $options = ['query' => ['purge' => 'true']]; // currently required by Orchestrate

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
        }

        return $this->isSuccess();
    }

    /**
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function purge()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
            .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);

        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
        }

        return $this->isSuccess();
    }

    private function setTimestampAndOrdinalFromLocation()
    {
        // Location: /v0/collection/key/events/type/1398286518286/6

        $location = $this->getResponse()->getHeader('Location');
        if (!$location)
            $location = $this->getResponse()->getHeader('Content-Location');

        $location = explode('/', trim($location, '/'));
        
        if (isset($location[5])) {
            $this->setTimestamp($location[5]);
        }

        if (isset($location[6])) {
            $this->setOrdinal($location[6]);
        }
    }
}
