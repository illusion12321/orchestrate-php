<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


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
     * @var \GuzzleHttp\Message\Response
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


    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }


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
        return $this->response ? $this->response->getHeader('X-ORCHESTRATE-REQ-ID') : '';
    }

    /**
     * @return string
     */
    public function getRequestDate()
    {
        return $this->response ? $this->response->getHeader('Date') : '';
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->response ? $this->response->getEffectiveUrl() : '';
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
        return !$this->statusCode || $this->statusCode >= 400 && $this->statusCode <= 599;
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