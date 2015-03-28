<?php
namespace andrefelipe\Orchestrate;

// use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\Response;

/**
 * Interface implement the basis HTTP client methods
 */
interface HttpClientInterface
{
    /**
     * @return string
     */
    public function getApiKey();

    /**
     * @param string $key
     *
     * @return HttpClient self
     */
    public function setApiKey($key);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @param string $host
     *
     * @return HttpClient self
     */
    public function setHost($host);

    /**
     * @return string
     */
    public function getApiVersion();

    /**
     * @param string $version
     */
    // public function setApiVersion($version);

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping();

    /**
     * This method wraps both createRequest and send methods, and provides a consistent
     * return of Response instances.
     *
     * More information on the parameters please go to the Guzzle docs.
     *
     * @param string     $method  HTTP method (GET, POST, PUT, etc.)
     * @param string|Url $url     HTTP URL to connect to
     * @param array      $options Array of options to apply to the request
     *
     * @return Response
     * @link http://docs.guzzlephp.org/clients.html#request-options
     */
    public function request($method, $url = null, array $options = []);
}
