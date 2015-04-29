<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Message\Response;

/**
 * Interface implementing the minimun required HTTP client methods.
 */
interface HttpClientInterface
{

    /**
     * @param string $key
     */
    public function setApiKey($key);

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping();

    /**
     * This method wraps both createRequest and send methods, and provides a consistent
     * return of a Response object.
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
