<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\KeyTrait;
use andrefelipe\Orchestrate\Common\RefTrait;
use andrefelipe\Orchestrate\Common\ValueTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractObject
{
    use KeyTrait;
    use RefTrait;
    use ValueTrait;

    public function __construct($collection, $key = null)
    {
        parent::__construct($collection);
        $this->setKey($key);
    }

    /**
     * @return array
     */
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
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->key = null;
        $this->ref = null;
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

            elseif ($key === 'value')
                $this->setValue((array) $value);
        }

        return $this;
    }

    /**
     * @param string $ref
     * 
     * @return KeyValue self
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
        if ($this->isSuccess()) {
            $this->setValue($this->body);
            $this->setRefFromETag();
        }
        else {
            $this->resetValue();
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
    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? $this->data : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $newValue];

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

            if ($value === null) {
                $this->data = $newValue;
            }            
        }

        return $this;
    }

    /**
     * @param array $value
     * 
     * @return KeyValue self
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value = null)
    {
        if ($value === null) {
            $value = $this->data;
        }

        // request
        $this->request('POST', $this->getCollection(true), ['json' => $value]);
        
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
    public function patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
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
    public function patchMerge(array $value = null, $ref = null, $reload = false)
    {
        if ($value === null) {
            $value = $this->data;
        }

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
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
    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
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
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
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
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;
        
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
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;

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
}
