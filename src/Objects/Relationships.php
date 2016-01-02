<?php
namespace andrefelipe\Orchestrate\Objects;

class Relationships extends AbstractList implements RelationshipsInterface
{
    use Properties\CollectionTrait; // ??? what?
    use Properties\KeyTrait;
    use Properties\DepthTrait;

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     */
    public function __construct($collection = null, $key = null, $kind = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setDepth($kind);
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_depth = null;
    }

    /**
     * @param array $data
     * @return Relationships
     */
    public function init(array $data)
    {
        if (!empty($data)) {
            parent::init($data);

            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
            if (isset($data['depth'])) {
                $this->setDepth($data['depth']);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;

        if (!empty($this->_key)) {
            $data['key'] = $this->_key;
        }
        if (!empty($this->_depth)) {
            $data['depth'] = $this->_depth;
        }

        return $data;
    }

    public function get($limit = 10, $offset = 0)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
        .'/relations/'.implode('/', $this->getDepth(true));

        $parameters = ['limit' => $limit];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    /**
     * @param array $itemValues
     */
    protected function createInstance(array $itemValues)
    {
        if (!empty($itemValues['path']['kind'])) {
            $kind = $itemValues['path']['kind'];

            if ($kind === 'item') {
                // TODO wrong!!! list Relationships objects will return Relationship itens right!!!???
                $item = (new KeyValue())->init($itemValues);

                if ($client = $this->getHttpClient()) {
                    $item->setHttpClient($client);
                }
                return $item;
            }
        }
        return null;
    }
}
