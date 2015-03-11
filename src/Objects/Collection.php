<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Query\KeyRangeBuilder;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Collection extends AbstractList
{
    /**
     * @var array
     */
    private $_aggregates = [];

    /**
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValueInterface Construct a collection item instance. A KeyValue or a custom class you set with setChildClass().
     */
    public function item($key = null, $ref = null)
    {
        return $this->getChildClass()
            ->newInstance($this->getCollection(true), $key, $ref)
            ->setClient($this->getClient(true));
    }

    /**
     * @return float
     */
    public function getAggregates()
    {
        return $this->_aggregates;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if (!empty($this->_aggregates)) {
            $result['aggregates'] = $this->_aggregates;
        }
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->_aggregates = [];
    }

    /**
     * Gets a lexicographically ordered list of items contained in a collection,
     * specified by the limit and key range parameters.
     * 
     * If there are more results available, the pagination URL can be checked with
     * getNextUrl/getPrevUrl, and queried with nextPage/prevPage methods.
     * 
     * @param int $limit The limit of items to return. Defaults to 10 and max to 100.
     * @param KeyRangeBuilder $range
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function get($limit = 10, KeyRangeBuilder $range = null)
    {
        // define request options
        $parameters = $range ? $range->toArray() : [];
        $parameters['limit'] = $limit > 100 ? 100 : $limit;

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);
        
        return $this->isSuccess();
    }

    /**
     * Deletes a collection. Warning this will permanently erase all data within
     * this collection and cannot be reversed!
     * 
     * To prevent accidental deletions, provide the current collection name as
     * the parameter. The collection will only be deleted if both names match.
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function delete($collectionName)
    {
        if ($collectionName === $this->getCollection(true)) {

            $response = $this->request(
                'DELETE',
                $this->getCollection(),
                ['query' => ['force' => 'true']]
            );

            return $response->getStatusCode() === 204;
        }

        return false;        
    }

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $parameters = [
            'query' => $query,
            'limit'=> $limit,
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

        return $this->isSuccess();
    }

    protected function request($method, $url = null, array $options = [])
    {
        parent::request($method, $url, $options);

        if ($this->isSuccess()) {

            if (isset($this->body['aggregates'])) {
                $this->_aggregates = (array) $this->body['aggregates'];
            }
        }
    }
}
