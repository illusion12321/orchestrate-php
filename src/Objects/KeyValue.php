<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Common\KeyTrait;
use andrefelipe\Orchestrate\Objects\Common\RefTrait;
use andrefelipe\Orchestrate\Objects\Common\ValueTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractObject
{
    use KeyTrait;
    use RefTrait;
    use ValueTrait;


    public function __construct($collection, $key=null)
    {
        parent::__construct($collection);
        $this->key = $key;
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
        
        return $result;
    }



    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->ref = null;
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

            elseif ($key === 'value')
                $this->data = (array) $value;
        }

        return $this;
    }







    // API



    /**
     * @param string $ref
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
     * @param array $value
     * @param string $ref
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
     * @param array $value
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
     * @param array|PatchBuilder $operations
     * @param string $ref
     * @return KeyValue self
     */
    public function patch($operations, $ref=null)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();
        
        if (is_a($operations, '\andrefelipe\Orchestrate\Query\PatchBuilder')) {
            $operations = $operations->toArray();

        } elseif (!is_array($operations)) {
            throw new \BadMethodCallException('The operations parameter can only be of type array or PatchBuilder ('.gettype($operations).' given).');
        }
        

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['json' => $operations];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // get the Value from API
            // $this->get($this->getRef());
        }
        
        return $this;
    }




    /**
     * @param array $value
     * @param string $ref
     * @return KeyValue self
     */
    public function patchMerge(array $value=null, $ref=null)
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
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // modify value, following Orchestrate's standard
            $this->data = array_merge($this->data, $value);

            foreach ($value as $k => $v) {
                if ($v === null) {
                    unset($this->data[$key]);
                }
            }

            // - Any fields that do not already exist will be added.

            // -If the partial Key/Value contains a field whose value is null, Orchestrate will remove the field and its value from the existing Key/Value item.
            
            // - Array values given in the partial Key/Value will not merge into an existing array value, but will completely replace the value in the existing Key/Value item.
        }

        return $this;
    }





    /**
     * @param string $ref
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