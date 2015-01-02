<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


// TODO method to move object to another collection
// TODO method to move object to another application
// TODO implement archival and tombstone properties like the ruby client

// TODO maybe move the hasChanged up to AbstractObject, then even the ArrayAccess etc


class KeyValue extends AbstractObject implements \ArrayAccess, \Countable, \IteratorAggregate
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
    protected $value = [];
    
    /**
     * @var boolean
     */
    protected $hasChanged = false;




    // TODO try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send


    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
    }


    /**
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        $this->hasChanged = true;
    }


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
        $this->hasChanged = true;
    }


    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
        $this->hasChanged = true;
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
        $this->hasChanged = true;
    }

    /**
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->hasChanged;
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
            $this->hasChanged = false;
        }
        else {
            $this->value = [];
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
            if ($this->hasChanged) {
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
            $this->hasChanged = false;
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
            if ($this->hasChanged) {
                $value = $this->value;
            } else {
                return $this;
            }
        }

        // request
        $this->request('POST', $this->collection, ['json' => $value]);
        
        // set values
        if ($this->isSuccess()) {
            $this->hasChanged = false;
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
        
        if ($this->isSuccess()) {
            $this->hasChanged = false;
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
            $this->hasChanged = false;
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
        $this->hasChanged = true;
    }

    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
        $this->hasChanged = true;
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