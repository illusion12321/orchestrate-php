<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;
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
     * @param Application $application
     * @param string $name
     */
    public function __construct(Application $application, $name)
    {
        $this->setApplication($application);
        $this->setCollection($name);
    }

    /**
     * @param string $key
     * @param string $ref
     * 
     * @return KeyValue Construct a collection item instance. A KeyValue or a custom class you set with setChildClass().
     */
    public function item($key = null, $ref = null)
    {
        return $this->getChildClass()
            ->newInstance($this->getCollection(true), $key, $ref)
            ->setApplication($this->getApplication(true));
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
     * @param int $limit
     * @param array $range
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function get($limit, array $range = null)
    {
        // define request options
        $parameters = ['limit' => $limit];

        if ($range) {
            if (isset($range['start']))
                $parameters['startKey'] = $range['start'];

            if (isset($range['after']))
                $parameters['afterKey'] = $range['after'];

            if (isset($range['before']))
                $parameters['beforeKey'] = $range['before'];

            if (isset($range['end']))
                $parameters['endKey'] = $range['end'];
        }

        // request
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);
        
        return $this->isSuccess();
    }

    // if ($this->_totalCount === null) {

    //     // get from Orchestrate
    //     $response = $this->getApplication(true)
    //         ->request('GET', $this->getCollection(true), ['query' => ['limit' => 1]]);

    //     if ($response) {
    //         $body = $response->json();
    //         print_r($body);
            
    //         if (!empty($body['total_count'])) {
    //             $this->_totalCount = (int) $body['total_count'];
    //         }
    //     }                
    // }

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function delete()
    {
        $response = $this->request(
            'DELETE',
            $this->getCollection(true),
            ['query' => ['force' => 'true']]
        );

        return $response->getStatusCode() === 204;
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
