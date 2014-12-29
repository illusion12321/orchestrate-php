<?php
namespace andrefelipe\Orchestrate\Response;

use GuzzleHttp\Message\ResponseInterface;


// TODO dynamic pagination (iterators, etc)

class ResponseSearch extends Response
{
    protected $collection;
    protected $results;
    protected $count;
    protected $totalCount;
    protected $nextUrl;
    protected $prevUrl;


    public function __construct(ResponseInterface $httpResponse, $collection)
    {
        parent::__construct($httpResponse);
        
        $this->collection = $collection;

        if ($this->success()) {
            $body = $httpResponse->json();
            $this->results = $body['results'];
        }
    }




    public function getCollection()
    {
        return $this->collection;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    public function getPrevUrl()
    {
        return $this->prevUrl;
    }








}