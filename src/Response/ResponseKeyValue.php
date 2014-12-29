<?php
namespace andrefelipe\Orchestrate\Response;

use andrefelipe\Orchestrate\Client;
use GuzzleHttp\Message\ResponseInterface;


class ResponseKeyValue extends Response
{
    protected $collection;
    protected $key;
    protected $ref;
    protected $value;

    protected $client;


    public function __construct(ResponseInterface $httpResponse, $collection, $key, $ref)
    {
        parent::__construct($httpResponse);
        
        $this->collection = $collection;
        $this->key = $key;
        $this->ref = $ref;

        if ($this->success()) {

            if ($etag = $this->getETag()) {
                $this->ref = trim($etag, '"');
            }      

            $this->value = $httpResponse->json();
        }
    }

    public function setClientReference(Client &$client)
    {
        $this->client = $client;
    }

/*
    private function parsePath()
    {
        // Content-Location: /v0/collection/key/refs/ad39c0f8f807bf40

        $path = [];

        $location = $this->http_response->getHeader('Location');
        if (!$location)
            $location = $this->http_response->getHeader('Content-Location');

        $location = explode('/', trim($location, '/'));
        if ($location)
        {
            $path = [
                'collection' => isset($location[1]) ? $location[1] : '',
                'key' => isset($location[2]) ? $location[2] : '',
                'ref' => isset($location[4]) ? $location[4] : '',
            ];
        }

        return $path;
    }
    */



    public function getCollection()
    {
        return $this->collection;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getValue()
    {
        return $this->value;
    }



}