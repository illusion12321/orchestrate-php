<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;
use GuzzleHttp\HasDataTrait;
use GuzzleHttp\ToArrayInterface;

abstract class AbstractObject implements ToArrayInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    use HasDataTrait;
    

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
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
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


    
    public function reset()
    {
        $this->response = null;
        $this->body = [];
        $this->status = '';
        $this->statusCode = 0;
        $this->statusMessage = '';        
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
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

            // try to get the Orchestrate error messages

            if (isset($this->body['code'])) {
                $this->status = $this->body['code'];
            }

            if (isset($this->body['message'])) {
                $this->statusMessage = $this->body['message'];
            }
        }
    }



    

    // helpers

    protected function noCollectionException()
    {
        if (!$this->collection) {
            throw new \BadMethodCallException('There is no collection set yet. Please do so through setCollection() method.');
        }
    }

    


}