<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Collection;
use andrefelipe\Orchestrate\Objects\KeyValue;
use andrefelipe\Orchestrate\Objects\KeyValues;
use andrefelipe\Orchestrate\Objects\Refs;
use andrefelipe\Orchestrate\Objects\Event;
use andrefelipe\Orchestrate\Objects\Events;
use andrefelipe\Orchestrate\Objects\Search;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;


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
	 * @var ClientInterface
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
     * @param string $key 
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
     * @return ClientInterface
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
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }



    /**
     * Create a new request based on the HTTP method.
     *
     * This method accepts an associative array of request options. Below is a
     * brief description of each parameter. See
     * http://docs.guzzlephp.org/clients.html#request-options for a much more
     * in-depth description of each parameter.
     *
     * - headers: Associative array of headers to add to the request
     * - body: string|resource|array|StreamInterface request body to send
     * - json: mixed Uploads JSON encoded data using an application/json Content-Type header.
     * - query: Associative array of query string values to add to the request
     * - auth: array|string HTTP auth settings (user, pass[, type="basic"])
     * - version: The HTTP protocol version to use with the request
     * - cookies: true|false|CookieJarInterface To enable or disable cookies
     * - allow_redirects: true|false|array Controls HTTP redirects
     * - save_to: string|resource|StreamInterface Where the response is saved
     * - events: Associative array of event names to callables or arrays
     * - subscribers: Array of event subscribers to add to the request
     * - exceptions: Specifies whether or not exceptions are thrown for HTTP protocol errors
     * - timeout: Timeout of the request in seconds. Use 0 to wait indefinitely
     * - connect_timeout: Number of seconds to wait while trying to connect. (0 to wait indefinitely)
     * - verify: SSL validation. True/False or the path to a PEM file
     * - cert: Path a SSL cert or array of (path, pwd)
     * - ssl_key: Path to a private SSL key or array of (path, pwd)
     * - proxy: Specify an HTTP proxy or hash of protocols to proxies
     * - debug: Set to true or a resource to view handler specific debug info
     * - stream: Set to true to stream a response body rather than download it all up front
     * - expect: true/false/integer Controls the "Expect: 100-Continue" header
     * - config: Associative array of request config collection options
     * - decode_content: true/false/string to control decoding content-encoding responses
     *
     * @param string     $method  HTTP method (GET, POST, PUT, etc.)
     * @param string|Url $url     HTTP URL to connect to
     * @param array      $options Array of options to apply to the request
     *
     * @return \GuzzleHttp\Message\Response
     * @link http://docs.guzzlephp.org/clients.html#request-options
     */
    public function request($method, $url = null, array $options = [])
    {
        $request = $this->getClient()->createRequest($method, $url, $options);

        try {
            $response = $this->getClient()->send($request);
        }
        catch (ClientException $e) {

            // have Orchestrate error message
            $response = $e->getResponse();
        }
        catch (ConnectException $e) {

            // assemble the best possible error response
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $response = new Response(
                    0,
                    ['Date' => gmdate('D, d M Y H:i:s').' GMT'],
                    null,
                    ['reason_phrase' => $e->getMessage()
                ]);
            }

            if ($request = $e->getRequest()) {
                $response->setEffectiveUrl($request->getUrl());
            }

        } catch (\Exception $e) {
            $response = new Response(
                500,
                ['Date' => 'Thu, 24 Oct 2013 15:20:42 GMT'],
                null,
                ['reason_phrase' => 'Probably a Request Timeout'
            ]);
        }

        return $response;
    }











    // -------------------- Orchestrate API --------------------
    // https://orchestrate.io/docs/apiref



    // Application

    /**
     * @return boolean
     */
    public function ping()
    {
    	$response = $this->request('HEAD');
    	return $response->getStatusCode() === 200;
    }


    // Collection

    /**
     * @return boolean
     */
    public function deleteCollection($collection)
    {
        return (new Collection($this, $collection))->deleteCollection();
    }


    // Key/Value

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function get($collection, $key, $ref=null)
    {
        return (new KeyValue($this, $collection, $key))->get($ref);
    }

    /**
     * @param string $collection
     * @param string $key
     * @param array $value
     * @param string $ref
     * @return KeyValue
     */
    public function put($collection, $key, array $value, $ref=null)
    {
        return (new KeyValue($this, $collection, $key))->put($value, $ref);
    }

    /**
     * @param string $collection
     * @param array $value
     * @return KeyValue
     */
    public function post($collection, array $value)
    {
        return (new KeyValue($this, $collection))->post($value);
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function delete($collection, $key, $ref=null)
    {
        return (new KeyValue($this, $collection, $key))->delete($ref);
    }

    /**
     * @param string $collection
     * @param string $key
     * @return KeyValue
     */
    public function purge($collection, $key)
    {
        return (new KeyValue($this, $collection, $key))->purge();
    }

    /**
     * @param string $collection
     * @param int $limit
     * @param array $range
     * @return KeyValues
     */
    public function listCollection($collection, $limit=10, array $range=null)
    {
        return (new KeyValues($this, $collection))->listCollection($limit, $range);
    }
    

    // Refs

    /**
     * @param string $collection
     * @param string $key
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * @return Refs
     */
    public function listRefs($collection, $key, $limit=10, $offset=0, $values=false)
    {
        return (new Refs($this, $collection, $key))->listRefs($limit, $offset, $values);
    }


    // Search

    /**
     * @param string $collection
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search
     */
    public function search($collection, $query, $sort='', $limit=10, $offset=0)
    {
        return (new Search($this, $collection))->search($query, $sort, $limit, $offset);
    }


    // Events

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @return Event
     */
    public function getEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        return (new Event($this, $collection, $key, $type, $timestamp, $ordinal))->get();
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param array $value
     * @param string $ref
     * @return Event
     */
    public function putEvent($collection, $key, $type, $timestamp, $ordinal, array $value, $ref=null)
    {
        return (new Event($this, $collection, $key, $type, $timestamp, $ordinal))->put($value, $ref);
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param array $value
     * @param int $timestamp
     * @return Event
     */
    public function postEvent($collection, $key, $type, array $value, $timestamp=0)
    {
        return (new Event($this, $collection, $key, $type))->post($value, $timestamp);
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param string $ref
     * @return Event
     */
    public function deleteEvent($collection, $key, $type, $timestamp, $ordinal, $ref=null)
    {
        return (new Event($this, $collection, $key, $type, $timestamp, $ordinal))->delete($ref);
    }

   /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @return Event
     */
    public function purgeEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        return (new Event($this, $collection, $key, $type, $timestamp, $ordinal))->purge();
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $limit
     * @param array $range
     * @return Events
     */
    public function listEvents($collection, $key, $type, $limit=10, array $range=null)
    {
        return (new Events($this, $collection, $key, $type))->listEvents($limit, $range);
    }


    





}