<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\RefTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;
use andrefelipe\Orchestrate\Objects\Properties\TimestampTrait;
use andrefelipe\Orchestrate\Objects\Properties\OrdinalTrait;

class Event extends AbstractObject
{
    use CollectionTrait;
    use KeyTrait;
    use RefTrait;
    use TypeTrait;
    use TimestampTrait;
    use OrdinalTrait;

    public function __construct($collection, $key = null, $type = null, $timestamp = 0, $ordinal = 0)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
        $this->setTimestamp($timestamp);
        $this->setOrdinal($ordinal);
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
                'key' => $this->getKey(),
                'ref' => $this->getRef(),
                'type' => $this->getType(),
                'timestamp' => $this->getTimestamp(),
                'ordinal' => $this->getOrdinal(),
            ],
            'value' => parent::toArray(),
            'timestamp' => $this->getTimestamp(),
            'ordinal' => $this->getOrdinal(),
        ];

        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_ref = null;
        $this->_type = null;
        $this->_timestamp = 0;
        $this->_ordinal = 0;
        $this->resetValue();
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
                $this->setCollection($value);

            elseif ($key === 'key')
                $this->setKey($value);

            elseif ($key === 'ref')
                $this->setRef($value);

            elseif ($key === 'type')
                $this->setType($value);

            elseif ($key === 'timestamp')
                $this->setTimestamp($value);

            elseif ($key === 'ordinal')
                $this->setOrdinal($value);

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
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/events/'.$this->getType(true).'/'.$this->getTimestamp(true)
            .'/'.$this->getOrdinal(true);
        
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
            $this->setRefFromETag();

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
    public function post(array $value = null, $timestamp = 0)
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
            $this->_ref = null;
            $this->_timestamp = 0;
            $this->_ordinal = 0;
            $this->setRefFromETag();
            $this->setTimestampFromLocation();
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
        }

        return $this->isSuccess();
    }

    protected function setTimestampFromLocation()
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
