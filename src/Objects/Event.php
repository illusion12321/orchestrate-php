<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\ReftimeTrait;
use andrefelipe\Orchestrate\Objects\Properties\RefTrait;
use andrefelipe\Orchestrate\Objects\Properties\TimestampTrait;
use andrefelipe\Orchestrate\Objects\Properties\TypeTrait;

class Event extends AbstractItem implements EventInterface
{
    use CollectionTrait;
    use KeyTrait;
    use ReftimeTrait;
    use RefTrait;
    use TimestampTrait;
    use TypeTrait;

    /**
     * @var int
     */
    private $_ordinal = null;

    /**
     * @var string
     */
    private $_ordinalStr = null;

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     */
    public function __construct($collection = null, $key = null, $type = null, $timestamp = null, $ordinal = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
        $this->setTimestamp($timestamp);
        $this->setOrdinal($ordinal);
    }

    public function getOrdinal($required = false)
    {
        if ($required) {
            $this->noOrdinalException();
        }

        return $this->_ordinal;
    }

    public function setOrdinal($ordinal)
    {
        $this->_ordinal = (int) $ordinal;

        return $this;
    }

    public function getOrdinalStr()
    {
        return $this->_ordinalStr;
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_type = null;
        $this->_timestamp = null;
        $this->_ordinal = null;
        $this->_ref = null;
        $this->_reftime = null;
        $this->_ordinalStr = null;
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
                } elseif ($key === 'type') {
                    $this->setType($value);
                } elseif ($key === 'timestamp') {
                    $this->setTimestamp($value);
                } elseif ($key === 'ordinal') {
                    $this->setOrdinal($value);
                } elseif ($key === 'ref') {
                    $this->setRef($value);
                } elseif ($key === 'reftime') {
                    $this->_reftime = (int) $value;
                } elseif ($key === 'ordinal_str') {
                    $this->_ordinalStr = $value;
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                }
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = [
            'kind' => 'event',
            'path' => [
                'collection' => $this->getCollection(),
                'kind' => 'event',
                'key' => $this->getKey(),
                'type' => $this->getType(),
                'timestamp' => $this->getTimestamp(),
                'ordinal' => $this->getOrdinal(),
                'ref' => $this->getRef(),
                'reftime' => $this->getReftime(),
                'ordinal_str' => $this->getOrdinalStr(),
            ],
            'value' => parent::toArray(),
            'timestamp' => $this->getTimestamp(),
            'ordinal' => $this->getOrdinal(),
            'reftime' => $this->getReftime(),
        ];

        return $data;
    }

    public function get()
    {
        // define request options
        $path = $this->getCollection(true) . '/' . $this->getKey(true) . '/events/'
        . $this->getType(true) . '/' . $this->getTimestamp(true) . '/' . $this->getOrdinal(true);

        // request
        $this->request('GET', $path);

        // set values
        if ($this->isSuccess()) {
            $this->init($this->getBody());
        }

        return $this->isSuccess();
    }

    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true) . '/' . $this->getKey(true) . '/events/'
        . $this->getType(true) . '/' . $this->getTimestamp(true) . '/' . $this->getOrdinal(true);

        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"' . $ref . '"'];
        }

        // request
        $this->request('PUT', $path, $options);

        // set values
        if ($this->isSuccess()) {
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->setRefFromETag();
            $this->setTimestampAndOrdinalFromLocation();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    public function post(array $value = null, $timestamp = null)
    {
        $path = $this->getCollection(true) . '/' . $this->getKey(true)
        . '/events/' . $this->getType(true);

        if ($timestamp === true) {
            $timestamp = $this->getTimestamp();
        }
        if ($timestamp) {
            $path .= '/' . $timestamp;
        }

        $newValue = $value === null ? parent::toArray() : $value;

        // request
        $this->request('POST', $path, ['json' => $newValue]);

        // set values
        if ($this->isSuccess()) {
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->setRefFromETag();
            $this->setTimestampAndOrdinalFromLocation();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true) . '/' . $this->getKey(true) . '/events/'
        . $this->getType(true) . '/' . $this->getTimestamp(true) . '/' . $this->getOrdinal(true);

        $options = ['query' => ['purge' => 'true']]; // currently required by Orchestrate

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"' . $ref . '"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        // update values
        if ($this->isSuccess()) {
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->resetValue();
        }

        return $this->isSuccess();
    }

    public function purge()
    {
        // define request options
        $path = $this->getCollection(true) . '/' . $this->getKey(true) . '/events/'
        . $this->getType(true) . '/' . $this->getTimestamp(true) . '/' . $this->getOrdinal(true);

        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);

        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->_timestamp = null;
            $this->_ordinal = null;
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->resetValue();
        }

        return $this->isSuccess();
    }

    /**
     * @throws \BadMethodCallException if 'ordinal' is not set yet.
     */
    protected function noOrdinalException()
    {
        if (!$this->_ordinal) {
            throw new \BadMethodCallException('There is no ordinal set yet. Please do so through setOrdinal() method.');
        }
    }

    private function setTimestampAndOrdinalFromLocation()
    {
        // Location: /v0/collection/key/events/type/1398286518286/6

        $location = $this->getResponse()->getHeader('Location');
        if (!$location) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }

        $location = explode('/', trim($location, '/'));

        if (isset($location[5])) {
            $this->setTimestamp($location[5]);
        } else {
            $this->_timestamp = null;
        }

        if (isset($location[6])) {
            $this->setOrdinal($location[6]);
        } else {
            $this->_ordinal = null;
        }
    }
}
