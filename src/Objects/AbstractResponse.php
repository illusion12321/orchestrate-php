<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ApplicationTrait;
use \GuzzleHttp\Message\Response;

abstract class AbstractResponse
{
    use ApplicationTrait;

    /**
     * @var array
     */
    protected $body = [];
    
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @var string
     */
    protected $status = '';

    /**
     * @var string
     */
    protected $statusCode = 0;

    /**
     * @var string
     */
    protected $statusMessage = '';

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
        return $this->body;
    }

    /**
     * Get the Guzzle Response object of the last request.
     * 
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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
        return $this->status;
    }

    /**
     * Gets the status code.
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
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
        return $this->statusMessage;
    }

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header.
     * 
     * @return string
     */
    public function getRequestId()
    {
        return $this->response
            ? $this->response->getHeader('X-ORCHESTRATE-REQ-ID')
            : '';
    }

    /**
     * Gets the Date header.
     * 
     * @return string
     */
    public function getRequestDate()
    {
        return $this->response
            ? $this->response->getHeader('Date')
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
        return $this->response
            ? $this->response->getEffectiveUrl()
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
        return !$this->statusCode
            || ($this->statusCode >= 400 && $this->statusCode <= 599);
    }

    /**
     * Store Guzzle Response and define body JSON and status.
     * 
     * @param Response $response
     */
    protected function setResponse(Response $response)
    {
        // store
        $this->response = $response;

        // process
        if ($response) {
            $this->body = $response->json();
            $this->statusMessage = $response->getReasonPhrase();
            $this->status = $this->statusMessage;
            $this->statusCode = $response->getStatusCode();

            if ($this->isError()) {

                // try to get the Orchestrate error messages

                if (isset($this->body['code'])) {
                    $this->status = $this->body['code'];
                }

                if (isset($this->body['message'])) {
                    $this->statusMessage = $this->body['message'];
                }
            }
        }
    }

    /**
     * Resets current object for reuse.
     */
    public function reset()
    {
        $this->response = null;
        $this->body = [];
        $this->status = '';
        $this->statusCode = 0;
        $this->statusMessage = '';
    }
    
    protected function request($method, $url = null, array $options = [])
    {
        // request at the Application HTTP client
        $response = $this->getApplication(true)->request($method, $url, $options);

        // and store/process the results
        $this->setResponse($response);
    }
}
