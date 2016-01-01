<?php
namespace andrefelipe\Orchestrate\Objects;

class Relationship extends AbstractItem implements RelationshipInterface
{
    use Properties\RelationTrait;
    use Properties\RelationshipTrait;
    use Properties\RefTrait;
    use Properties\ReftimeTrait;
    use Properties\TimestampTrait; // TODO why? again?
    use Properties\ScoreTrait;

    /**
     * @param KeyValueInterface $source
     * @param string $kind
     * @param KeyValueInterface $destination
     */
    public function __construct(
        KeyValueInterface $source = null,
                          $kind = null,
        KeyValueInterface $destination = null
    ) {
        if ($source) {
            $this->setSource($source);
        }
        if ($kind) {
            $this->setRelation($kind);
        }
        if ($destination) {
            $this->setDestination($destination);
        }
    }

    public function reset()
    {
        parent::reset();
        $this->_source = null;
        $this->_relation = null;
        $this->_destination = null;
        $this->_ref = null;
        $this->_reftime = null;
        $this->_timestamp = null;
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

                if ($key === 'source') {
                    $this->setSource((new KeyValue())->init($value));
                } elseif ($key === 'destination') {
                    $this->setDestination((new KeyValue())->init($value));
                } elseif ($key === 'relation') {
                    $this->setRelation($value);
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                } elseif ($key === 'ref') {
                    $this->setRef($value);
                } elseif ($key === 'reftime') {
                    $this->setReftime($value);
                } elseif ($key === 'timestamp') {
                    $this->setTimestamp($value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [

            'path' => [
                'kind' => self::KIND,
                'source' => null,
                'destination' => null,
                'relation' => $this->getRelation(),
                'ref' => $this->getRef(),
            ],
            'kind' => self::KIND,
            // 'timestamp' => $this->getTimestamp(), //TODO Why again?
        ];

        $source = $this->getSource();
        if ($source) {
            $data['path']['source'] = [
                'kind' => 'item',
                'collection' => $source->getCollection(),
                'key' => $source->getKey(),
            ];
        }

        $destination = $this->getDestination();
        if ($destination) {
            $data['path']['destination'] = [
                'kind' => 'item',
                'collection' => $destination->getCollection(),
                'key' => $destination->getKey(),
            ];
        }

        $value = parent::toArray();
        if (!empty($value)) {
            $data['value'] = $value;
        }

        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }

        $reftime = $this->getReftime();
        if (!empty($reftime)) {
            $data['path']['reftime'] = $reftime;
            $data['reftime'] = $reftime;
        }

        return $data;
    }

    public function get()
    {
        // define request options
        $path = $this->formRelationPath();

        // Orchestrate doesn't support relationship history (refs) yet
        // if ($ref) {
        //     $path .= '/refs/'.trim($ref, '"');
        // }

        // request
        $this->request('GET', $path);

        // set values
        if ($this->isSuccess()) {
            $this->setValue($this->getBody());
            $this->setRefFromETag();
        }
        return $this->isSuccess();
    }

    public function put(array $value = null, $ref = null, $both_ways = false)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->formRelationPath();
        $options = ['json' => empty($newValue) ? null : $newValue];

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

        if ($this->isSuccess()) {

            // set values
            $this->setRefFromETag();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }

            // put both ways
            if ($both_ways) {
                $path = $this->formRelationPath(false, true);
                $this->request('PUT', $path, $options);
            }
        }
        return $this->isSuccess();
    }

    public function delete($both_ways = false)
    {
        $options = ['query' => ['purge' => 'true']];

        $this->request('DELETE', $this->formRelationPath(), $options);

        if ($both_ways && $this->isSuccess()) {
            $this->request('DELETE', $this->formRelationPath(false, true), $options);
        }

        if ($this->isSuccess()) {
            $this->_score = null;
            // $this->_distance = null;
            $this->_ref = null;
            $this->_reftime = null;
            $this->resetValue();
        }

        return $this->isSuccess();
    }

}
