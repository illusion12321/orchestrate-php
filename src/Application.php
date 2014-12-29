<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Response\Response;


use andrefelipe\Orchestrate\Collection;
use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\Search;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Exception\ClientException;


class Application
{
	/**
	 * @var string
	 */
	protected $host;
	
	/**
	 * @var string
	 */
	protected $apiVersion;

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var \GuzzleHttp\ClientInterface
	 */
	protected $client;



	/**
	 * @param string $apiKey
	 */
	public function __construct($apiKey=null, $host=null, $apiVersion=null)
	{
        // set client options
        $this->setApiKey($apiKey);
        $this->setHost($host);
        $this->setApiVersion($apiVersion);
	}




    // -------------------- Http Client --------------------

    /**
     * @param string $apiKey 
     */
    public function setApiKey($key=null)
    {
        if ($key)
            $this->apiKey = $key;
        else
            $this->apiKey = getenv('ORCHESTRATE_API_KEY');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host 
     */
    public function setHost($host=null)
    {
        if ($host)
            $this->host = trim($host, '/'); 
        else
            $this->host = 'https://api.orchestrate.io';
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $version 
     */
    public function setApiVersion($version=null)
    {
        $this->apiVersion = $version ? $version : 'v0';
    }


    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient()
    {
        if (!$this->client)
        {
            // create the default http client
            $this->client = new \GuzzleHttp\Client(
            [
                'base_url' => $this->host.'/'.$this->apiVersion.'/',
                'defaults' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [ $this->apiKey, null ],
                ]
            ]);
        }

        return $this->client;
    }

    /**
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function setClient(\GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }



    
    public function request($method, $url = null, array $options = [])
    {
        $request = $this->getClient()->createRequest($method, $url, $options);

        return $this->send($request);
    }


    private function send(RequestInterface $request)
    {
        try {
            $response = $this->getClient()->send($request);
        }
        catch (ClientException $e)
        {
            $response = $e->getResponse();
        }

        return $response;
    }









    // -------------------- Orchestrate Objects --------------------

    public function collection($collection)
    {
        return new Collection($this, $collection);
    }








    // -------------------- Orchestrate API --------------------
    // https://orchestrate.io/docs/apiref


    /**
     * @return boolean
     */
    public function ping()
    {
    	$response = $this->getClient()->head();
    	return $response->getStatusCode() === 200;
    }


    // Key/Value

    /**
     * @return KeyValue
     */
    public function get($collection, $key, $ref=null)
    {
        return (new KeyValue($this, $collection, $key, $ref))->get($ref);
    }

    /**
     * @return KeyValue
     */
    public function put($collection, $key, array $value, $ref=null)
    {
        return (new KeyValue($this, $collection, $key, $ref))->put($value, $ref);
    }



    // Search

    /**
     * @return Search
     */
    public function search($collection, $query, $sort='', $limit=10, $offset=0)
    {
        return (new Search($this, $collection))->get($query, $sort, $limit, $offset);
    }




    





}