<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\RefTrait;
use andrefelipe\Orchestrate\Objects\Common\ValueTrait;
use andrefelipe\Orchestrate\Objects\Common\TypeTrait;
use andrefelipe\Orchestrate\Objects\Common\TimestampTrait;

class Event extends AbstractObject
{
    use KeyTrait;
    use RefTrait;
    use ValueTrait;
    use TypeTrait;
    use TimestampTrait;

    /**
     * @var int
     */
    protected $ordinal = 0;

    public function __construct($collection, $key=null, $type=null, $timestamp=0, $ordinal=0)
    {
        parent::__construct($collection);
        $this->key = $key;
        $this->type = $type;
        $this->timestamp = $timestamp;
        $this->ordinal = $ordinal;
    }

    /**
     * @return int
     */
    public function getOrdinal()
    {
        return $this->ordinal;
    }

    /**
     * @param int $ordinal
     */
    public function setOrdinal($ordinal)
    {
        $this->ordinal = (int) $ordinal;
    }    
    
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'event',
            'path' => [
                'collection' => $this->collection,
                'key' => $this->key,
                'ref' => $this->ref,
                'type' => $this->type,
                'timestamp' => $this->timestamp,
                'ordinal' => $this->ordinal,
            ],
            'value' => $this->data,
            'timestamp' => $this->timestamp,
            'ordinal' => $this->ordinal,
        ];

        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->ref = null;
        $this->type = null;
        $this->timestamp = 0;
        $this->ordinal = 0;
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

            elseif ($key === 'key')
                $this->key = $value;

            elseif ($key === 'ref')
                $this->ref = $value;

            elseif ($key === 'type')
                $this->type = $value;

            elseif ($key === 'timestamp')
                $this->timestamp = (int) $value;

            elseif ($key === 'ordinal')
                $this->ordinal = (int) $value;

            elseif ($key === 'value')
                $this->data = (array) $value;
        }

        return $this;
    }

    /**
     * @return Event self
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function get()
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();
        $this->noTimestampException();
        $this->noOrdinalException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$this->type.'/'.$this->timestamp.'/'.$this->ordinal;

     
        // request
        $this->request('GET', $path);

        // set values
        $this->ref = null;

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
     * @param array $value
     * @param string $ref
     * 
     * @return Event self
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function put(array $value=null, $ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();
        $this->noTimestampException();
        $this->noOrdinalException();

        if ($value === null) {
            $value = $this->data;
        }

        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$this->type.'/'.$this->timestamp.'/'.$this->ordinal;
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
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
     * @param array $value
     * @param int $timestamp
     * 
     * @return Event self
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function post(array $value=null, $timestamp=0)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();

        $path = $this->collection.'/'.$this->key.'/events/'.$this->type;

        if ($timestamp === true) {
            $timestamp = $this->timestamp;
        }        
        if ($timestamp) {
            $path .= '/'.$timestamp;
        }

        if ($value === null) {
            $value = $this->data;
        }

        // request
        $this->request('POST', $path, ['json' => $value]);
        
        // set values
        if ($this->isSuccess()) {
            $this->ref = null;
            $this->timestamp = 0;
            $this->ordinal = 0;
            $this->setRefFromETag();
            $this->setTimestampFromLocation();
        }

        // set value as input value, even if not success, so we can retry
        $this->data = $value;

        return $this;
    }

    /**
     * @param string $ref
     * 
     * @return Event self
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function delete($ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();
        $this->noTimestampException();
        $this->noOrdinalException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$this->type.'/'.$this->timestamp.'/'.$this->ordinal;
        $options = ['query' => ['purge' => 'true']];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->ref = null;
        }

        return $this;
    }

    /**
     * 
     * @return Event self
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function purge()
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        $this->noTypeException();
        $this->noTimestampException();
        $this->noOrdinalException();

        // define request options
        $path = $this->collection.'/'.$this->key.'/events/'.$this->type.'/'.$this->timestamp.'/'.$this->ordinal;
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);

        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->ref = null;
        }

        return $this;
    }

    protected function setTimestampFromLocation()
    {
        // Location: /v0/collection/key/events/type/1398286518286/6

        $location = $this->response->getHeader('Location');
        if (!$location)
            $location = $this->response->getHeader('Content-Location');

        $location = explode('/', trim($location, '/'));
        
        if (isset($location[5])) {
            $this->timestamp = (int) $location[5];
        }

        if (isset($location[6])) {
            $this->ordinal = (int) $location[6];
        }
    }

    protected function noOrdinalException()
    {
        if (!$this->ordinal) {
            throw new \BadMethodCallException('There is no ordinal set yet. Please do so through setOrdinal() method.');
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || (int) $offset === $offset) {
           throw new \RuntimeException('Sorry, indexed arrays not allowed at the root of Event objects.');
        } else {
            $this->data[$offset] = $value;
        }
    }
}
