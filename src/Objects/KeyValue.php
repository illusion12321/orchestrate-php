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

    /**
     * @param string $ref
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
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
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
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
            $this->data = $value;
        }

        return $this;
    }

    /**
     * @param array $value
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
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
            $this->data = $value;
        }

        return $this;
    }

    /**
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(PatchBuilder $operations, $ref=null, $reload=false)
    {
        // required values
        $this->noCollectionException();
        $this->noKeyException();

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['json' => $operations->toArray()];

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

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }
        }
        
        return $this;
    }

    /**
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(array $value=null, $ref=null, $reload=false)
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

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }            
        }

        return $this;
    }

    /**
     * @param string $ref
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
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
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
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

    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#graph-put
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
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#graph-delete
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

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) || is_int($offset)) {
           throw new \RuntimeException('Sorry, indexed arrays not allowed at the root of KeyValue objects.');
        } else {
            $this->data[$offset] = $value;
        }
    }
}
