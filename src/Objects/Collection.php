<?php
namespace andrefelipe\Orchestrate\Objects;


class Collection extends AbstractList
{


    // Collection

    /**
     * @param int $limit
     * @param string $startKey
     * @param string $afterKey
     * @param string $beforeKey
     * @param string $endKey
     * @return Collection self
     */
    public function listCollection($limit=10, $startKey='', $afterKey='', $beforeKey='', $endKey='')
    {
        // define request options
        $parameters = ['limit' => $limit];

        if ($startKey)
            $parameters['startKey'] = $startKey;
       
        if ($afterKey)
            $parameters['afterKey'] = $afterKey;

        if ($beforeKey)
            $parameters['beforeKey'] = $beforeKey;

        if ($endKey)
            $parameters['endKey'] = $endKey;

        // request
        $this->request('GET', $this->collection, ['query' => $parameters]);
        
        return $this;
    }

    /**
     * @return Collection self
     */
    public function deleteCollection()
    {
        // request
        $this->request('DELETE', $this->collection, ['query' => ['force' => 'true']]);
        
        return $this;
    }


    // Key/Value

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function get($key, $ref=null)
    {
        return $this->application->get($this->collection, $key, $ref);
    }

    /**
     * @param string $key
     * @param array $value
     * @param string $ref
     * @return KeyValue
     */
    public function put($key, array $value, $ref=null)
    {
        return $this->application->put($this->collection, $key, $value, $ref);
    }

    /**
     * @param array $value
     * @return KeyValue
     */
    public function post(array $value)
    {
        return $this->application->post($this->collection, $value);
    }

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function delete($key, $ref=null)
    {
        return $this->application->delete($this->collection, $key, $ref, $purge);
    }

    /**
     * @param string $key
     * @return KeyValue
     */
    public function purge($key)
    {
        return $this->application->purge($this->collection, $key);
    }


    // Refs
    
    /**
     * @return Refs
     */
    public function listRefs($key, $limit=10, $offset=0, $values=false)
    {
        return $this->application->listRefs($this->collection, $key, $limit, $offset, $values);
    }


    // Search

    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search
     */
    public function search($query, $sort='', $limit=10, $offset=0)
    {
        return $this->application->search($this->collection, $query, $sort, $limit, $offset);
    }

}