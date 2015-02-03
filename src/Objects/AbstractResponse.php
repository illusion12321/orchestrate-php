<?php
namespace andrefelipe\Orchestrate\Objects;

use \GuzzleHttp\Message\Response;

abstract class AbstractResponse
{
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
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Always lowercase, spaces as underline.
     * 
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->response
            ? $this->response->getHeader('X-ORCHESTRATE-REQ-ID')
            : '';
    }

    /**
     * @return string
     */
    public function getRequestDate()
    {
        return $this->response
            ? $this->response->getHeader('Date')
            : '';
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->response
            ? $this->response->getEffectiveUrl()
            : '';
    }
    
    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return !$this->isError();
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return !$this->statusCode
            || ($this->statusCode >= 400 && $this->statusCode <= 599);
    }

    public function reset()
    {
        $this->response = null;
        $this->body = [];
        $this->status = '';
        $this->statusCode = 0;
        $this->statusMessage = '';        
    }

    protected function setResponse(Response $response)
    {
        // store
        $this->response = $response;
        
        // process
        $this->body = $response->json();
        $this->statusMessage = $response->getReasonPhrase();
        $this->status = str_replace(' ', '_', strtolower($this->statusMessage));
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
