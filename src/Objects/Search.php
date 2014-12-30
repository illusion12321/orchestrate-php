<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


// TODO dynamic pagination (iterators, etc)


class Search extends AbstractObject
{
        

    /**
     * @var int
     */
    protected $totalCount = 0;

    /**
     * @var string
     */
    protected $nextUrl = '';

    /**
     * @var string
     */
    protected $prevUrl = '';

    /**
     * @var array
     */
    protected $results = [];



    /**
     * @param Application $application
     * @param string $collection
     */
    public function __construct(Application $application, $collection)
    {
        parent::__construct($application, $collection);
    }


    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this);
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @return string
     */
    public function getPrevUrl()
    {
        return $this->prevUrl;
    }



    // TODO review this function name, may be useful to change to 'search'
    // but still wait to the pagination methods, like getNext / getPrev

    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search self
     */
    public function get($query, $sort='', $limit=10, $offset=0)
    {
        // define request options
        $options = [
            'query' => [
                'query' => $query,
                'sort' => $sort,
                'limit'=> $limit,
                'offset' => $offset,
            ]
        ];

        // request
        $this->request('GET', $this->collection, $options);

        // set values
        if ($this->isSuccess()) {
                
            $this->results = (array) $this->body['results'];
            $this->totalCount = (int) $this->body['total_count'];
            $this->nextUrl = !empty($this->body['next']) ? $this->body['next'] : '';
            $this->prevUrl = !empty($this->body['prev']) ? $this->body['prev'] : '';

        } else {

            $this->totalCount = 0;
            $this->nextUrl = '';
            $this->prevUrl = '';
            $this->results = [];
        }

        return $this;
    }

    



    // ArrayAccess

    public function offsetExists($offset)
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->results[] = $value;
        } else {
            $this->results[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);
    }


    // Countable

    public function count()
    {
        return count($this->results);
    }


    // IteratorAggregate

    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }




}