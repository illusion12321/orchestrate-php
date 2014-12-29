<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Response\Response;
use andrefelipe\Orchestrate\Response\ResponseKeyValue;
use andrefelipe\Orchestrate\Response\ResponseSearch;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Exception\ClientException;


class Client
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
	protected $httpClient;



	/**
	 * @param string $apiKey
	 */
	public function __construct($apiKey=null, $host=null, $apiVersion=null)
	{
        $this->setApiKey($apiKey);
        $this->setHost($host);
        $this->setApiVersion($apiVersion);
	}



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
	public function getHttpClient()
	{
        if (!$this->httpClient)
        {
            // create the default http client
            $this->httpClient = new \GuzzleHttp\Client(
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

	    return $this->httpClient;
	}

	/**
	 * @param \GuzzleHttp\ClientInterface $httpClient
	 */
	public function setHttpClient(\GuzzleHttp\ClientInterface $httpClient)
	{
	    $this->httpClient = $httpClient;
	}









    // Orchestrate API
    // https://orchestrate.io/docs/apiref


    /**
     * @return boolean
     */
    public function ping()
    {
    	$response = $this->getHttpClient()->head();
    	return $response->getStatusCode() === 200;
    }




    //get : $this->send($this->createRequest('GET', $url, $options));

    public function get($collection, $key, $ref=null)
    {
        $path = $collection.'/'.$key;

        if ($ref) {
            $path .= '/refs/'.trim($ref, '"');
        }            

        $request = $this->getHttpClient()->createRequest('GET', $path);

    	return new ResponseKeyValue($this->send($request), $collection, $key, $ref);
    }


    public function put($collection, $key, array $value, $ref=null)
    {
        $path = $collection.'/'.$key;

        // TODO if match
        // if ($ref)
        //     $path .= '/refs/'.$ref;

        $request = $this->getHttpClient()->createRequest('PUT', $path, [
            'json' => $value
        ]);

    	return new Response($this->send($request));
    }



    public function search($collection, $query, $sort='', $limit=10, $offset=0)
    {
        $request = $this->getHttpClient()->createRequest('GET', $collection, [
            'query' => [
                'query' => $query,
                'sort' => $sort,
                'limit'=> $limit,
                'offset' => $offset,
            ]
        ]);

        return new ResponseSearch($this->send($request), $collection);
    }




    

    private function send(RequestInterface $request)
    {
        try {
            $response = $this->getHttpClient()->send($request);
        }
        catch (ClientException $e)
        {
            $response = $e->getResponse();
        }

        return $response;
    }



}