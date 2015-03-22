<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Message\Response;

/**
 * Class that implements the ClientInterface methods and the children classes.
 *
 * @link https://orchestrate.io/docs/apiref
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var string
     */
    private $_host = 'https://api.orchestrate.io';

    /**
     * @var string
     */
    private $_apiVersion = 'v0';

    /**
     * @var string
     */
    private $_apiKey;

    /**
     * @var HttpClientInterface
     */
    private $_client;

    /**
     * @param string $apiKey
     * @param string $host
     */
    public function __construct($apiKey = null, $host = null)
    {
        $this->setApiKey($apiKey)
             ->setHost($host);
        // ->setApiVersion($apiVersion);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * @param string
     *
     * @return AbstractClient self
     */
    public function setApiKey($key)
    {
        if ($key) {
            $this->_apiKey = $key;
        } else {
            $this->_apiKey = getenv('ORCHESTRATE_API_KEY');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param string $host
     *
     * @return AbstractClient self
     */
    public function setHost($host)
    {
        if ($host) {
            $this->_host = rtrim($host, '/');
            $this->_host = rtrim($host, '/v0');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->_apiVersion;
    }

    /**
     * @param string $version
     *
     * @return AbstractClient self
     */
    // public function setApiVersion($version)
    // {
    //     $this->_apiVersion = $version ? $version : 'v0';

    //     return $this;
    // }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        if (!$this->_client) {
            // create the default http client
            $this->_client = new HttpClient([
                'base_url' => $this->getHost() . '/' . $this->getApiVersion() . '/',
                'defaults' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [$this->getApiKey(), null],
                ],
            ]);
        }

        return $this->_client;
    }

    /**
     * @param HttpClientInterface $client
     *
     * @return AbstractClient self
     */
    public function setHttpClient(HttpClientInterface $client)
    {
        $this->_client = $client;

        return $this;
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
     * @return Response
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
                    ['Date' => gmdate('D, d M Y H:i:s') . ' GMT'],
                    null,
                    ['reason_phrase' => $e->getMessage()]
                );
            }

            if ($request = $e->getRequest()) {
                $response->setEffectiveUrl($request->getUrl());
            }

        } catch (\Exception $e) {
            $response = new Response(
                500,
                ['Date' => gmdate('D, d M Y H:i:s') . ' GMT'],
                null,
                ['reason_phrase' => 'Probably a Request Timeout']
            );
        }

        return $response;
    }
}
