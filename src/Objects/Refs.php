<?php
namespace andrefelipe\Orchestrate\Objects;

class Refs extends AbstractList implements RefsInterface
{
    use Properties\CollectionTrait;
    use Properties\ItemClassTrait;
    use Properties\KeyTrait;

    public function __construct($collection = null, $key = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {
            parent::init($data);

            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;

        if (!empty($this->_key)) {
            $data['key'] = $this->_key;
        }

        return $data;
    }

    public function get($limit = 10, $offset = 0, $values = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true).'/refs/';

        $parameters = ['limit' => $limit];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        if ($values) {
            $parameters['values'] = 'true';
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
                $class = $this->getItemClass();

            } else {
                return null;
            }

            $item = $class->newInstance()->init($itemValues);

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
