<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;
use GuzzleHttp\Message\ResponseInterface;


abstract class AbstractObject implements \ArrayAccess, \Countable, \IteratorAggregate
{
    

    /**
     * @var \andrefelipe\Orchestrate\Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $collection;

    /**
     * @var array
     */
    protected $body = [];



    
    /**
     * @var \GuzzleHttp\Message\ResponseInterface
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

    

    
    



    public function __construct(Application $application, $collection)
    {
        $this->application = $application;
        $this->collection = $collection;
    }



    public function getCollection()
    {
        return $this->collection;
    }



    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    public function getRequestId()
    {
        return $this->response ? $this->response->getHeader('X-ORCHESTRATE-REQ-ID') : '';
    }

    public function getRequestDate()
    {
        return $this->response ? $this->response->getHeader('Date') : '';
    }

    public function getRequestUrl()
    {
        return $this->response ? $this->response->getEffectiveUrl() : '';
    }
    

    public function isSuccess()
    {
        return !$this->isError();
    }

    public function isError()
    {
        return $this->statusCode >= 400 && $this->statusCode <= 599;
    }




    protected function request($method, $url = null, array $options = [])
    {
        // request
        $this->response = $this->application->request($method, $url, $options);

        // set body
        $this->body = $this->response->json();

        // set status
        $this->statusMessage = $this->response->getReasonPhrase();
        $this->status = str_replace(' ', '_', strtolower($this->statusMessage));
        $this->statusCode = $this->response->getStatusCode();

        if ($this->isError()) {

            if (isset($this->body['code'])) {
                $this->status = $this->body['code'];
            }

            if (isset($this->body['message'])) {
                $this->statusMessage = $this->body['message'];
            }
        }
    }





    

}