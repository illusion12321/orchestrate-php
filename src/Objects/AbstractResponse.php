<?php
namespace andrefelipe\Orchestrate\Objects;

use \GuzzleHttp\Message\Response;

abstract class AbstractResponse extends AbstractConnection
{
    // TODO create some interfaces here? ResponseInterface

    /**
     * @var array
     */
    private $_body = null;

    /**
     * @var Response
     */
    private $_response = null;

    /**
     * Gets the body of the response, independently if it was an error or not.
     * Useful for debugging but for a more specific usage please rely on each
     * implementation getters.
     *
     * @return array
     */
    public function getBody()
    {
        if (!is_array($this->_body)) {
            $this->_body = [];
        }
        return $this->_body;
    }

    /**
     * Get the Guzzle Response object of the last request.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Gets the status of the last response.
     * If the request was successful the value is the HTTP Reason-Phrase.
     * If the request was not successful the value is the Orchestrate Error Description.
     *
     * @return string
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatus()
    {
        return $this->_response ? $this->_response->getReasonPhrase() : '';
    }

    /**
     * Gets the status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_response ? $this->_response->getStatusCode() : 0;
    }

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header.
     *
     * @return string
     */
    public function getRequestId()
    {
        return $this->_response ? $this->_response->getHeader('X-ORCHESTRATE-REQ-ID') : '';
    }

    /**
     * Gets the Date header from the response. Note, it's the request date generated from
     * the Orchestrate service, not our application request.
     *
     * @return string
     */
    public function getRequestDate()
    {
        return $this->_response ? $this->_response->getHeader('Date') : '';
    }

    /**
     * Gets the effective URL that was generated for the request.
     * Useful for debugging or logging, etc.
     *
     * Sample for a KeyValue GET:
     * https://api.orchestrate.io/v0/my-collection/my-key
     *
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->_response ? $this->_response->getEffectiveUrl() : '';
    }

    /**
     * Check if last request was successful.
     *
     * A request is considered successful if status code is not 4xx or 5xx.
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return !$this->isError();
    }

    /**
     * Check if last request was unsuccessful.
     *
     * A request is considered error if status code is 4xx or 5xx.
     *
     * @return boolean
     */
    public function isError()
    {
        $code = $this->getStatusCode();
        return !$code || ($code >= 400 && $code <= 599);
    }

    /**
     * Resets current object for reuse.
     */
    public function reset()
    {
        $this->_response = null;
        $this->_body = null;
    }

    /**
     * Request the current HTTP client and store the response and json body internally.
     *
     * More information on the parameters please go to the Guzzle docs.
     *
     * @param string     $method  HTTP method (GET, POST, PUT, etc.)
     * @param string|Url $url     HTTP URL to connect to
     * @param array      $options Array of options to apply to the request
     *
     * @link http://docs.guzzlephp.org/clients.html#request-options
     */
    protected function request($method, $url = null, array $options = [])
    {
        // request
        $this->_response = $this->getHttpClient(true)->request($method, $url, $options);

        // set body
        if ($this->_response) {
            $this->_body = $this->_response->json();
        } else {
            $this->_body = null;
        }
    }
}
