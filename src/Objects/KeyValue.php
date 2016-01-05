<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractItem implements KeyValueInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\RefTrait;
    use Properties\ReftimeTrait;
    use Properties\ScoreTrait;
    use Properties\DistanceTrait;

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

    public function isTombstone()
    {
        return $this->_tombstone;
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_ref = null;
        $this->_score = null;
        $this->_distance = null;
        $this->_reftime = null;
        $this->_tombstone = false;
        $this->resetValue();
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['path'])) {
                $data = array_merge($data, $data['path']);
            }

            foreach ($data as $key => $value) {
                if ($key === 'collection') {
                    $this->setCollection($value);
                } elseif ($key === 'key') {
                    $this->setKey($value);
                } elseif ($key === 'ref') {
                    $this->setRef($value);
                } elseif ($key === 'reftime') {
                    $this->setReftime($value);
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
                } elseif ($key === 'distance') {
                    $this->setDistance($value);
                } elseif ($key === 'tombstone') {
                    $this->_tombstone = (boolean) $value;
                }
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = [
            'kind' => static::KIND,
            'path' => [
                'collection' => $this->getCollection(),
                'kind' => static::KIND,
                'key' => $this->getKey(),
                'ref' => $this->getRef(),
            ],
            'value' => parent::toArray(),
        ];

        if ($this->_reftime !== null) {
            $data['path']['reftime'] = $this->_reftime;
        }

        if ($this->_tombstone) {
            $data['path']['tombstone'] = $this->_tombstone;
        }

        // search properties
        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }
        if ($this->_distance !== null) {
            $data['distance'] = $this->_distance;
        }

        return $data;
    }

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
            $this->setValue($this->getBody());
            $this->setRefFromETag();
        }
        return $this->isSuccess();
    }

    public function put(array $value = null)
    {
        return $this->_put($value);
    }

    public function putIf($ref = true, array $value = null)
    {
        if ($ref === true) {
            $ref = $this->getRef();
        }
        if (empty($ref) || !is_string($ref)) {
            throw new \BadMethodCallException('A valid \'ref\' value is required.');
        }

        return $this->_put($value, $ref);
    }

    public function putIfNone(array $value = null)
    {
        return $this->_put($value, false);
    }

    private function _put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $newValue];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        } elseif ($ref === false) {
            $options['headers'] = ['If-None-Match' => '"*"'];
        }

        // request
        $this->request('PUT', $path, $options);

        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }
        }
        return $this->isSuccess();
    }

    public function post(array $value = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // request
        $this->request('POST', $this->getCollection(true), ['json' => $newValue]);

        // set values
        if ($this->isSuccess()) {
            $this->setKeyRefFromLocation();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }
        }
        return $this->isSuccess();
    }

    public function patch(PatchBuilder $operations, $reload = false)
    {
        return $this->_patch($operations, null, $reload);
    }

    public function patchIf($ref = true, PatchBuilder $operations, $reload = false)
    {
        if ($ref === true) {
            $ref = $this->getRef();
        }
        if (empty($ref) || !is_string($ref)) {
            throw new \BadMethodCallException('A valid \'ref\' value is required.');
        }

        return $this->_patch($operations, $ref, $reload);
    }

    private function _patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $operations->toArray()];

        if ($ref) {
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

    public function patchMerge(array $value, $reload = false)
    {
        return $this->_patchMerge($value, $reload);
    }

    public function patchMergeIf($ref, array $value, $reload = false)
    {
        if ($ref === true) {
            $ref = $this->getRef();
        }
        if (empty($ref) || !is_string($ref)) {
            throw new \BadMethodCallException('A valid \'ref\' value is required.');
        }

        return $this->_patchMerge($value, $ref, $reload);
    }

    private function _patchMerge(array $value, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $value];

        if ($ref) {
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

    public function delete()
    {
        return $this->_delete();
    }

    public function deleteIf($ref = true)
    {
        if ($ref === true) {
            $ref = $this->getRef();
        }
        if (empty($ref) || !is_string($ref)) {
            throw new \BadMethodCallException('A valid \'ref\' value is required.');
        }

        return $this->_delete($ref);
    }

    private function _delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = [];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        if ($this->isSuccess()) {
            $this->_score = null;
            $this->_distance = null;
            $this->_reftime = null;
            $this->_tombstone = true;
            $this->resetValue();
        }
        return $this->isSuccess();
    }

    public function purge()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);

        if ($this->isSuccess()) {
            $this->_key = null;
            $this->_ref = null;
            $this->_score = null;
            $this->_distance = null;
            $this->_reftime = null;
            $this->_tombstone = false;
            $this->resetValue();
        }
        return $this->isSuccess();
    }

    public function refs()
    {
        return (new Refs($this->getCollection(true), $this->getKey(true)))
            ->setHttpClient($this->getHttpClient())
            ->setItemClass(new \ReflectionClass($this));
    }

    public function events($type = null)
    {
        return (new Events($this->getCollection(true), $this->getKey(true), $type))
            ->setHttpClient($this->getHttpClient());
    }

    public function event($type = null, $timestamp = null, $ordinal = null)
    {
        return (new Event(
            $this->getCollection(true),
            $this->getKey(true),
            $type,
            $timestamp,
            $ordinal
        ))->setHttpClient($this->getHttpClient());
    }

    public function relationships($kind)
    {
        return (new Relationships($this->getCollection(true), $this->getKey(true), $kind))
            ->setHttpClient($this->getHttpClient());
    }

    public function relationship($kind, KeyValueInterface $destination)
    {
        return (new Relationship($this, $kind, $destination))
            ->setHttpClient($this->getHttpClient());
    }

    /**
     * Helper to set the Key and Ref from a Orchestrate Location HTTP header.
     * For example: Location: /v0/collection/key/refs/ad39c0f8f807bf40
     *
     * Should be used when the request was succesful.
     */
    private function setKeyRefFromLocation()
    {
        $location = $this->getResponse()->getHeader('Location');
        if (empty($location)) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }
        if (empty($location)) {
            return;
        }

        $location = explode('/', trim($location[0], '/'));
        if (count($location) > 4) {
            $this->_key = $location[2];
            $this->_ref = $location[4];
        } else {
            $this->_key = null;
            $this->_ref = null;
        }
    }
}
