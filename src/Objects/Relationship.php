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

    // static KIND = 'relationship'; //TODO test and implement

    /**
     * @param KeyValueInterface $source
     * @param string $kind
     * @param KeyValueInterface $destination
     */
    public static function __construct(
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
            'kind' => 'relationship',
            'relation' => $this->getRelation(),
            'timestamp' => $this->getTimestamp(),
            'path' => [
                'kind' => 'relationship',
                'key' => $this->getKey(),
                'ref' => $this->getRef(),
            ];
            todo, terminarthis, talvezprecisarevisaropath, masnaverdadepodemandarbalaemigrartudoparaopath, screenshotnodesktop;
            'value' => parent::toArray(),
        ];

        $source = $this->getSource();
        if ($source) {
            $data['source'] = [
                'collection' => $source->getCollection(),
                'kind' => 'item',
                'key' => $source->getKey(),
            ];
        }

        $destination = $this->getDestination();
        if ($destination) {
            $data['destination'] = [
                'collection' => $destination->getCollection(),
                'kind' => 'item',
                'key' => $destination->getKey(),
            ];
        }

        $value = parent::toArray();
        if (!empty($value)) {
            $data['value'] = parent::toArray(),
        }

        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }

        return $data;
    }

    /**
     * Set the relation between the two objects.
     * Use the $bothWays parameter to set the relation both ways (2 API calls are made).
     *
     * @param boolean $bothWays
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function put($bothWays = false)
    {
        $this->request('PUT', $this->formRelationPath());

        if ($bothWays && $this->isSuccess()) {
            $this->request('PUT', $this->formRelationPath(true));
        }

        return $this->isSuccess();
    }

    public function putv(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->formRelationPath();
        $options = ['json' => empty($newValue) ? null : $newValue];

        // if ($ref) {

        //     // set If-Match
        //     if ($ref === true) {
        //         $ref = $this->getRef();
        //     }

        //     $options['headers'] = ['If-Match' => '"'.$ref.'"'];

        // } elseif ($ref === false) {

        //     // set If-None-Match
        //     $options['headers'] = ['If-None-Match' => '"*"'];
        // }

        // request
        $this->request('PUT', $path, $options);

        // set values
        if ($this->isSuccess()) {
            // $this->setRefFromETag();

            // if ($value !== null) {
            //     $this->resetValue();
            //     $this->setValue($newValue);
            // }
        }
        return $this->isSuccess();
    }

    /**
     * Remove the relation between the two objects.
     * Use the $bothWays parameter to remove the relation both ways (2 API calls are made).
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function delete($bothWays = false)
    {
        $options = ['query' => ['purge' => 'true']];

        $this->request('DELETE', $this->formRelationPath(), $options);

        if ($bothWays && $this->isSuccess()) {
            $this->request('DELETE', $this->formRelationPath(true), $options);
        }

        return $this->isSuccess();
    }

    /**
     * Helper to form the relation URL path
     *
     * @return string
     */
    private function formRelationPath($reverse = false)
    {
        $source = $this->getSource(true);
        $destination = $this->getDestination(true);

        if ($reverse) {
            $item = $source;
            $source = $destination;
            $destination = $item;
        }

        return $source->getCollection(true).'/'.$source->getKey(true)
        .'/relation/'.$this->getRelation(true).'/'
        .$destination->getCollection(true).'/'.$destination->getKey(true);
    }
}
