<?php
namespace andrefelipe\Orchestrate\Objects;

class Event extends AbstractItem implements EventInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\ReftimeTrait;
    use Properties\RefTrait;
    use Properties\TimestampTrait;
    use Properties\TypeTrait;
    use Properties\ScoreTrait;
    use Properties\OrdinalTrait;

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     */
    public function __construct(
        $collection = null,
        $key = null,
        $type = null,
        $timestamp = null,
        $ordinal = null
    ) {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
        $this->setTimestamp($timestamp);
        $this->setOrdinal($ordinal);
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_type = null;
        $this->_timestamp = null;
        $this->_ordinal = null;
        $this->_ordinalStr = null;
        $this->_ref = null;
        $this->_reftime = null;
        $this->_score = null;
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
                    $this->setReftime($value);
                } elseif ($key === 'ordinal_str') {
                    $this->setOrdinalStr($value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
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

        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }

        return $data;
    }

    public function get()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
        .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

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
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
        .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
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
        $path = $this->getCollection(true).'/'.$this->getKey(true)
        .'/events/'.$this->getType(true);

        if ($timestamp === true) {
            $timestamp = $this->getTimestamp();
        }
        if ($timestamp) {
            $path .= '/'.$timestamp;
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
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
        .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

        $options = ['query' => ['purge' => 'true']]; // currently required by Orchestrate

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        // update values
        if ($this->isSuccess()) {
            $this->_ref = null;
            $this->_reftime = null;
            $this->_ordinalStr = null;
            $this->_score = null;
            $this->resetValue();
        }
        return $this->isSuccess();
    }

    public function purge()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/events/'
        .$this->getType(true).'/'.$this->getTimestamp(true).'/'.$this->getOrdinal(true);

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
            $this->_score = null;
            $this->resetValue();
        }
        return $this->isSuccess();
    }

    private function setTimestampAndOrdinalFromLocation()
    {
        // Location: /v0/collection/key/events/type/1398286518286/6

        $location = $this->getResponse()->getHeader('Location');
        if (empty($location)) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }
        if (empty($location)) {
            return;
        }

        $location = explode('/', trim($location[0], '/'));

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
