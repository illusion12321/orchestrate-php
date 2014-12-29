<?php
namespace andrefelipe\Orchestrate;

namespace andrefelipe\Orchestrate\Application;


// TODO maybe convert to an Object, with proper array access, and item storage

class Collection
{

    /**
     * @var \andrefelipe\Orchestrate\Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $collection;



    /**
     * @param string $apiKey
     */
    public function __construct(Application $application, $collection)
    {
        $this->application = $application;
        $this->collection = $collection;
    }


    public function getApplication()
    {
        return $this->application;
    }

    public function getCollection()
    {
        return $this->collection;
    }



    public function get($key, $ref=null)
    {
        return $this->application->get($collection, $key, $ref);
    }

    public function put($key, array $value, $ref=null)
    {
        return $this->application->put($this->collection, $key, $value, $ref);
    }

    public function search($query, $sort='', $limit=10, $offset=0)
    {
        return $this->application->search($this->collection, $query, $sort, $limit, $offset);
    }


}