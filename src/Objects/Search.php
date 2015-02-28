<?php
namespace andrefelipe\Orchestrate\Objects;

class Search extends AbstractList
{
    /**
     * @var array
     */
    private $_aggregates = [];

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

    protected function createChildrenClass(array $values)
    {
        return (new SearchResult($this->getCollection()))
            ->setApplication($this->getApplication())
            ->init($values);
    }
}
