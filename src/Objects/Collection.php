<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Collection extends AbstractSearchList implements CollectionInterface
{
    use Properties\CollectionTrait;
    use Properties\ItemClassTrait;
    use Properties\EventClassTrait;
    use Properties\RelationshipClassTrait;

    /**
     * @param string $collection
     */
    public function __construct($collection = null)
    {
        $this->setCollection($collection);
    }

    public function item($key = null, $ref = null)
    {
        return $this->getItemClass()->newInstance()
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setRef($ref)
            ->setHttpClient($this->getHttpClient());
    }

    public function refs($key = null)
    {
        return (new Refs())
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setHttpClient($this->getHttpClient())
            ->setItemClass($this->getItemClass());
    }

    public function event($key = null, $type = null, $timestamp = null, $ordinal = null)
    {
        return $this->getEventClass()->newInstance()
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setType($type)
            ->setTimestamp($timestamp)
            ->setOrdinal($ordinal)
            ->setHttpClient($this->getHttpClient());
    }

    public function events($key = null, $type = null)
    {
        return (new Events())
            ->setCollection($this->getCollection(true))
            ->setKey($key)
            ->setType($type)
            ->setHttpClient($this->getHttpClient());
    }

    public function getTotalItems()
    {
        return $this->getItemCount($this->getCollection(true), KeyValue::KIND);
    }

    public function getTotalEvents($type = null)
    {
        return $this->getItemCount($this->getCollection(true), Event::KIND, $type);
    }

    public function getTotalRelationships($type = null)
    {
        return $this->getItemCount(
            $this->getCollection(true),
            Relationship::KIND,
            null,
            $type
        );
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (isset($data['itemClass'])) {
                $this->setItemClass($data['itemClass']);
            }
            if (isset($data['eventClass'])) {
                $this->setEventClass($data['eventClass']);
            }
            if (isset($data['relationshipClass'])) {
                $this->setRelationshipClass($data['relationshipClass']);
            }
            if (isset($data['collection'])) {
                $this->setCollection($data['collection']);
            }

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;
        $data['collection'] = $this->_collection;

        if ($this->getItemClass()->name !== self::$defaultItemClassName) {
            $data['itemClass'] = $this->getItemClass()->name;
        }
        if ($this->getEventClass()->name !== self::$defaultEventClassName) {
            $data['eventClass'] = $this->getEventClass()->name;
        }
        if ($this->getRelationshipClass()->name !== self::$defaultRelationshipClassName) {
            $data['relationshipClass'] = $this->getRelationshipClass()->name;
        }

        return $data;
    }

    public function get($limit = 10, KeyRangeBuilder $range = null)
    {
        // define request options
        $parameters = $range ? $range->toArray() : [];
        $parameters['limit'] = $limit > 100 ? 100 : $limit;

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    public function delete($collection_name)
    {
        if ($collection_name === $this->getCollection(true)) {

            $this->request('DELETE', $collection_name, ['query' => ['force' => 'true']]);

            if ($this->isSuccess()) {
                $this->setResponseValues();
            }
            return $this->getStatusCode() === 204;
        }

        return false;
    }

    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $parameters = [
            'query' => $query,
            'limit' => $limit,
        ];
        if (!empty($sort)) {
            $parameters['sort'] = implode(',', (array) $sort);
        }
        if (!empty($aggregate)) {
            $parameters['aggregate'] = implode(',', (array) $aggregate);
        }
        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);

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

            if ($kind === KeyValue::KIND) {
                $class = $this->getItemClass();

            } elseif ($kind === Event::KIND) {
                $class = $this->getEventClass();

            } elseif ($kind === Relationship::KIND) {
                $class = $this->getRelationshipClass();

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
