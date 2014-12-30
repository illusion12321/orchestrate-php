<?php
namespace andrefelipe\Orchestrate;

namespace andrefelipe\Orchestrate\Application;


// TODO maybe convert to an Object, with proper array access, and item storage? – can work fine code-wise, but have to confirm a real world usage


class Collection
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $collection;


    // idea
    /**
     * @var array of KeyValue
     */
    // protected $items;


    /**
     * @param Application $application
     * @param string $collection
     */
    public function __construct(Application $application, $collection)
    {
        $this->application = $application;
        $this->collection = $collection;
    }


    // public function getApplication()
    // {
    //     return $this->application;
    // }

    // public function getCollection()
    // {
    //     return $this->collection;
    // }


    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function get($key, $ref=null)
    {
        return $this->application->get($collection, $key, $ref);
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