<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;

class Relations extends AbstractList
{
    use KeyTrait;

    /**
     * @var array
     */
    private $_depth = null;

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     */
    public function __construct($collection = null, $key = null, $kind = null)
    {
        parent::__construct($collection);
        $this->setKey($key);
        $this->setDepth($kind);
    }

    /**
     * @param boolean $required
     *
     * @return array
     * @throws \BadMethodCallException if 'relation depth' is required but not set yet.
     */
    public function getDepth($required = false)
    {
        if ($required && empty($this->_depth)) {
            throw new \BadMethodCallException('There is no relation depth set yet. Do so through setDepth() method.');
        }

        return $this->_depth;
    }

    /**
     * @param string|array $kind
     *
     * @return self
     */
    public function setDepth($kind)
    {
        $this->_depth = (array) $kind;

        return $this;
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_depth = null;
    }

    /**
     * @param array $data
     * @return Relations
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
        $data['kind'] = 'relations';

        if (!empty($this->_key)) {
            $data['key'] = $this->_key;
        }
        if (!empty($this->_depth)) {
            $data['depth'] = $this->_depth;
        }

        return $data;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
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
