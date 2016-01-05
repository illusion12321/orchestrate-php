<?php
namespace andrefelipe\Orchestrate\Objects;

class Relationship extends AbstractItem implements RelationshipInterface
{
    use Properties\RelationTrait;
    use Properties\RelationshipTrait;
    use Properties\RefTrait;
    use Properties\ReftimeTrait;
    use Properties\ScoreTrait;
    use Properties\DistanceTrait;
    use Properties\ItemClassTrait;

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
        $this->_score = null;
        $this->_distance = null;
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
                    if (is_array($value)) {
                        $item = $this->getItemClass()->newInstance()->init($value);
                        $this->setSource($item);
                    } elseif ($value instanceof KeyValueInterface) {
                        $this->setSource($value);
                    }
                } elseif ($key === 'destination') {
                    if (is_array($value)) {
                        $item = $this->getItemClass()->newInstance()->init($value);
                        $this->setDestination($item);
                    } elseif ($value instanceof KeyValueInterface) {
                        $this->setDestination($value);
                    }
                } elseif ($key === 'relation') {
                    $this->setRelation($value);
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                } elseif ($key === 'ref') {
                    $this->setRef($value);
                } elseif ($key === 'reftime') {
                    $this->setReftime($value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
                } elseif ($key === 'distance') {
                    $this->setDistance($value);
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
            'kind' => static::KIND,
            'path' => [
                'kind' => static::KIND,
                'source' => null,
                'destination' => null,
                'relation' => $this->getRelation(),
                'ref' => $this->getRef(),
            ],
        ];

        $reftime = $this->getReftime();
        if (!empty($reftime)) {
            $data['path']['reftime'] = $reftime;
        }

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

        // search properties
        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }
        if ($this->_distance !== null) {
            $data['distance'] = $this->_distance;
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
        $path = $this->formRelationPath();
        $options = ['json' => empty($newValue) ? null : $newValue];

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

    public function putBoth(array $value = null)
    {
        return $this->_putBoth($value);
    }

    public function putBothIf($ref = true, array $value = null)
    {
        if ($ref === true) {
            $ref = $this->getRef();
        }
        if (empty($ref) || !is_string($ref)) {
            throw new \BadMethodCallException('A valid \'ref\' value is required.');
        }

        return $this->_putBoth($value, $ref);
    }

    public function putBothIfNone(array $value = null)
    {
        return $this->_putBoth($value, false);
    }

    private function _putBoth(array $value = null, $ref = null)
    {
        $success = $this->_put($value, $ref);

        if ($success) {
            $path = $this->formRelationPath(false, true);
            $value = parent::toArray();
            $options = ['json' => empty($value) ? null : $value];

            $this->request('PUT', $path, $options);
        }

        return $this->isSuccess();
    }

    public function delete()
    {
        $options = ['query' => ['purge' => 'true']];

        $this->request('DELETE', $this->formRelationPath(), $options);

        if ($this->isSuccess()) {
            $this->_score = null;
            $this->_distance = null;
            $this->_ref = null;
            $this->_reftime = null;
            $this->resetValue();
        }

        return $this->isSuccess();
    }

    public function deleteBoth()
    {
        $success = $this->delete();

        if ($success) {
            $path = $this->formRelationPath(false, true);
            $options = ['query' => ['purge' => 'true']];
            $this->request('DELETE', $path, $options);
        }

        return $this->isSuccess();
    }

}
