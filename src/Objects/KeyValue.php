<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;
use GuzzleHttp\Message\ResponseInterface;


// TODO method to move object to another collection
// TODO method to move object to another application
// TODO implement archival and tombstone properties like the ruby client

// TODO review this isDirty naming

class KeyValue extends AbstractObject
{
        
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $ref = null;


    /**
     * @var array
     */
    protected $value = []; //TODO test, can be null?
    
    /**
     * @var boolean
     */
    protected $isDirty = false;




    // TODO try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send


    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
    }


    // TODO maybe add setKey and setCollection after all

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }


    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue(array $value)
    {
        $this->value = $value;
    }

    /**
     * @return boolean
     */
    public function isDirty()
    {
        return $this->isDirty;
    }





    /**
     * @return KeyValue self
     */
    public function get($ref=null)
    {
        // require a key to be set
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
            $this->value = $this->body;
            $this->setRefFromETag();
            $this->isDirty = false;
        }
        else {
            $this->value = []; //TODO teste can be null?
        }

        return $this;
    }

    

    /**
     * @return KeyValue self
     */
    public function put(array $value=null, $ref=null)
    {
        // require a key to be set
        $this->noKeyException();

        if ($value === null) {
            if ($this->isDirty) {
                $value = $this->value;
            } else {
                return $this;
            }
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
            $this->isDirty = false;
        }

        // set value as input value, even if not success, so we can retry
        $this->value = $value;


        return $this;
    }



    /**
     * @return KeyValue self
     */
    public function post(array $value=null)
    {
        if ($value === null) {
            if ($this->isDirty) {
                $value = $this->value;
            } else {
                return $this;
            }
        }

        // request
        $this->request('POST', $this->collection, ['json' => $value]);
        
        // set values
        if ($this->isSuccess()) {
            $this->isDirty = false;
            $this->key = null;
            $this->ref = null;
            $this->setKeyRefFromLocation();
        }

        // set value as input value, even if not success, so we can retry
        $this->value = $value;

        return $this;
    }




    /**
     * @return KeyValue self
     */
    public function delete($ref=null)
    {
        // require a key to be set
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
        
        // TODO confirm if the success body is array

        if ($this->isSuccess()) {
            $this->isDirty = false;
        }

        return $this;
    }




    /**
     * @return KeyValue self
     */
    public function purge()
    {
        // require a key to be set
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);
        
        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->ref = null;
            $this->isDirty = false;
        }

        return $this;
    }












    // helpers
    private function noKeyException()
    {
        if (!$this->key) {
            throw new \BadMethodCallException('There is no key set yet. Please do so through setKey() method.');
        }
    }


    private function setRefFromETag()
    {
        if ($etag = $this->response->getHeader('ETag')) {
            $this->ref = trim($etag, '"');
        }
    }

    
    private function setKeyRefFromLocation()
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









    // ArrayAccess

    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
        $this->isDirty = true;
    }

    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
        $this->isDirty = true;
    }
    

    // Countable

    public function count()
    {
        return count($this->value);
    }
    

    // IteratorAggregate

    public function getIterator()
    {
        return new \ArrayIterator($this->value);
    }




}