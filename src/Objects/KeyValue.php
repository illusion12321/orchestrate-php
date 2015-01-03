<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


// TODO method to move object to another collection
// TODO method to move object to another application
// TODO implement archival and tombstone properties like the ruby client

// TODO maybe move the ArrayAccess up to AbstractObject

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
     * @var int
     */
    protected $refTime = 0;






    // TODO try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send


    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
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
    }

    /**
     * @return int
     */
    public function getRefTime()
    {
        return $this->refTime;
    }




    public function setValue(array $value)
    {
        $this->data = $value;
    }


    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->ref = null;
        $this->refTime = 0;
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












    // helpers

    private function noCollectionException()
    {
        if (!$this->collection) {
            throw new \BadMethodCallException('There is no collection set yet. Please do so through setCollection() method.');
        }
    }

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









    // override ArrayAccess

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || (int) $offset === $offset) {
           throw new \RuntimeException('Sorry, indexed arrays not allowed at the root of KeyValue objects.');
        } else {
            $this->data[$offset] = $value;
        }
    }






}