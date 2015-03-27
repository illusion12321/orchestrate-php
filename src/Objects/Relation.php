<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ToJsonInterface;
use andrefelipe\Orchestrate\Objects\Properties\TimestampTrait;

class Relation extends AbstractResponse implements
ToJsonInterface,
ReusableObjectInterface
{
    use TimestampTrait;

    /**
     * @var string
     */
    private $_relation = null;

    /**
     * @var KeyValueInterface
     */
    private $_source = null;

    /**
     * @var KeyValueInterface
     */
    private $_destination = null;

    /**
     * @param KeyValueInterface $source
     * @param string $kind
     * @param KeyValueInterface $destination
     */
    public function __construct(KeyValueInterface $source = null, $kind = null, KeyValueInterface $destination = null)
    {
        $this->setSource($source);
        $this->setRelation($kind);
        $this->setDestination($destination);
    }

    /**
     * Get the relation kind between the objects.
     *
     * @param boolean $required
     *
     * @return string
     */
    public function getRelation($required = false)
    {
        if ($required) {
            $this->noRelationException();
        }

        return $this->_relation;
    }

    /**
     * @param string $kind
     *
     * @return Relation self
     * @throws \InvalidArgumentException if 'kind' is array. Only one relation can be handled per time.
     */
    public function setRelation($kind)
    {
        if (is_array($kind)) {
            throw new \InvalidArgumentException('The kind parameter can not be Array. Only one relation can be handled per time.');
        }

        $this->_relation = (string) $kind;

        return $this;
    }

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     */
    public function getSource($required = false)
    {
        return $this->_source;
    }

    /**
     * @param KeyValueInterface $item
     *
     * @return Relation self
     */
    public function setSource(KeyValueInterface $item)
    {
        $this->_source = $item;

        return $this;
    }

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     */
    public function getDestination($required = false)
    {
        return $this->_destination;
    }

    /**
     * @param KeyValueInterface $item
     *
     * @return Relation self
     */
    public function setDestination(KeyValueInterface $item)
    {
        $this->_destination = $item;

        return $this;
    }

    public function reset()
    {
        parent::reset();
        $this->_source = null;
        $this->_relation = null;
        $this->_destination = null;
        $this->_timestamp = null;
    }

    public function init(array $data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $key => $value) {

            if ($key === 'source') {
                $this->setSource((new KeyValue())->init($value));
            } elseif ($key === 'destination') {
                $this->setDestination((new KeyValue())->init($value));
            } elseif ($key === 'relation') {
                $this->setRelation($value);
            } elseif ($key === 'timestamp') {
                $this->setTimestamp($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'relationship',
            'relation' => $this->getRelation(),
            'timestamp' => $this->getTimestamp(),
        ];

        $source = $this->getSource();
        if ($source) {
            $result['source'] = [
                'collection' => $source->getCollection(),
                'kind' => 'item',
                'key' => $source->getKey(),
            ];
        }

        $destination = $this->getDestination();
        if ($destination) {
            $result['destination'] = [
                'collection' => $destination->getCollection(),
                'kind' => 'item',
                'key' => $destination->getKey(),
            ];
        }

        return $result;
    }

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
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

        return $source->getCollection(true) . '/' . $source->getKey(true)
        . '/relation/' . $this->getRelation(true) . '/'
        . $destination->getCollection(true) . '/' . $destination->getKey(true);
    }

    /**
     * @throws \BadMethodCallException if 'relation' is not set yet.
     */
    protected function noRelationException()
    {
        if (empty($this->_relation)) {
            throw new \BadMethodCallException('There is no relation set yet. Please do so through setRelation() method.');
        }
    }
}
