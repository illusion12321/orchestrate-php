<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\ClientInterface;
use \GuzzleHttp\Message\Response;

abstract class AbstractResponse
{
    /**
     * @var array
     */
    private $_body = [];
    
    /**
     * @var Response
     */
    private $_response = null;

    /**
     * @var string
     */
    private $_status = '';

    /**
     * @var string
     */
    private $_statusCode = 0;

    /**
     * @var string
     */
    private $_statusMessage = 'Not loaded yet.';

    /**
     * @var ClientInterface 
     */
    private $_client;

    /**
     * Get current client instance, either of Application or Client class.
     * 
     * @param boolean $required
     * 
     * @return ClientInterface
     */
    public function getClient($required = false)
    {
        if ($required)
            $this->noClientException();

        return $this->_client;
    }

    /**
     * Set the client which the object will use to make API requests.
     * 
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->_client = $client;
        
        return $this;
    }

    /**
     * Gets the body of the response, independently if it was an error or not.
     * Useful for debugging but for a more specific usage please rely on each
     * implementation getters.
     * 
     * Important: The body is always an associative array.
     * 
     * @return array
     */
    public function getBody()
    {
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
     * If the request was not successful the value is the Orchestrate Error Code.
     * 
     * @return string
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Gets the status code.
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Gets the status message.
     * If the request was successful the value is the HTTP Reason-Phrase.
     * If the request was not successful the value is the Orchestrate Error Description.
     * 
     * @return string
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatusMessage()
    {
        return $this->_statusMessage;
    }

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header.
     * 
     * @return string
     */
    public function getRequestId()
    {
        return $this->_response
            ? $this->_response->getHeader('X-ORCHESTRATE-REQ-ID')
            : '';
    }

    /**
     * Gets the Date header.
     * 
     * @return string
     */
    public function getRequestDate()
    {
        return $this->_response
            ? $this->_response->getHeader('Date')
            : '';
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
        return $this->_response
            ? $this->_response->getEffectiveUrl()
            : '';
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
        return !$this->_statusCode
            || ($this->_statusCode >= 400 && $this->_statusCode <= 599);
    }

    /**
     * Store Guzzle Response and define body JSON and status.
     * 
     * @param Response $response
     */
    protected function setResponse(Response $response)
    {
        // store
        $this->_response = $response;

        // process
        if ($response) {
            $this->_body = $response->json();
            $this->_statusMessage = $response->getReasonPhrase();
            $this->_status = $this->_statusMessage;
            $this->_statusCode = $response->getStatusCode();

            if ($this->isError()) {

                // try to get the Orchestrate error messages

                if (isset($this->_body['code'])) {
                    $this->_status = $this->_body['code'];
                }

                if (isset($this->_body['message'])) {
                    $this->_statusMessage = $this->_body['message'];
                }
            }
        } else {
            $this->_body = [];
            $this->_status = 'Internal Server Error';
            $this->_statusCode = 500;
            $this->_statusMessage = 'Invalid Response';
        }
    }

    /**
     * Resets current object for reuse.
     */
    public function reset()
    {
        $this->_response = null;
        $this->_body = [];
        $this->_status = '';
        $this->_statusCode = 0;
        $this->_statusMessage = '';
    }
    
    protected function request($method, $url = null, array $options = [])
    {
        // request at the Client HTTP client
        $response = $this->getClient(true)->request($method, $url, $options);

        // and store/process the results
        $this->setResponse($response);
    }

    /**
     * @throws \BadMethodCallException if 'client' is not set yet.
     */
    protected function noClientException()
    {
        if (!$this->_client) {
            throw new \BadMethodCallException('There is no client set yet. Please do so through setClient() method.');
        }
    }
}
