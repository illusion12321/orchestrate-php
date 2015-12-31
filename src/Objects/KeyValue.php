<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractItem implements KeyValueInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\ReftimeTrait;
    use Properties\RefTrait;
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
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
                } elseif ($key === 'distance') {
                    $this->setDistance($value);
                } elseif ($key === 'reftime') {
                    $this->setReftime($value);
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
            'kind' => 'item',
            'path' => [
                'collection' => $this->getCollection(),
                'kind' => 'item',
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

    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
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

    public function patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $operations->toArray()];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
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
        return $this->isSuccess();
    }

    public function patchMerge(array $value, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
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
        return $this->isSuccess();
    }

    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = [];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

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

        // this new reflection instance may be cached, just check subclasses scope
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

    public function relations($kind)
    {
        return (new Relations($this->getCollection(true), $this->getKey(true), $kind))
            ->setHttpClient($this->getHttpClient());
    }

    public function relation($kind, KeyValueInterface $destination)
    {
        return (new Relation($this, $kind, $destination))
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
