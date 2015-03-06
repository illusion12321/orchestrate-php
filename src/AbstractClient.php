<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var string
     */
    private $host;
    
    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;
    
    /**
     * @param string $apiKey
     * @param string $host
     * @param string $apiVersion
     */
    public function __construct($apiKey = null, $host = null, $apiVersion = null)
    {
        // set client options
        $this->setApiKey($apiKey);
        $this->setHost($host);
        $this->setApiVersion($apiVersion);
    }

    
    public function setApiKey($key = null)
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
    public function setHost($host = null)
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
    public function setApiVersion($version = null)
    {
        $this->apiVersion = $version ? $version : 'v0';
    }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
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
    public function setHttpClient(\GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        $response = $this->request('HEAD');
        return $response->getStatusCode() === 200;
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
        $request = $this->getHttpClient()->createRequest($method, $url, $options);

        try {
            $response = $this->getHttpClient()->send($request);
        
        } catch (ClientException $e) {
            // get Orchestrate error message
            $response = $e->getResponse();
        
        } catch (ConnectException $e) {

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
                ['Date' => gmdate('D, d M Y H:i:s').' GMT'],
                null,
                ['reason_phrase' => 'Probably a Request Timeout'
            ]);
        }

        return $response;
    }
}
