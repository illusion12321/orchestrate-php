<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;

// use andrefelipe\Orchestrate\Client;
use andrefelipe\Orchestrate\Response;
use GuzzleHttp\Message\ResponseInterface;


abstract class AbstractObject implements \ArrayAccess, \Countable, \IteratorAggregate
{
    const STATUS_READY = 'ready';
    const STATUS_DIRTY = 'dirty';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

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
    protected $body;

    /**
     * @var array
     */
    protected $error;

    /**
     * @var string
     */
    protected $status;

     /**
     * @var \GuzzleHttp\Message\ResponseInterface
     */
    protected $response;




    public function __construct(Application $application, $collection)
    {
        $this->application = $application;
        $this->collection = $collection;
        $this->body = [];
        $this->error = false;
        $this->status = self::STATUS_READY;
    }



    public function getCollection()
    {
        return $this->collection;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getResponse() // ?
    {
        return $this->response;
    }

    protected function request($method, $url = null, array $options = [])
    {
        // request
        $this->response = $this->application->request($method, $url, $options);
        
        // set status
        $statusCode = $this->response->getStatusCode();
        $success = !($statusCode >= 400 && $statusCode <= 599);
        $this->status = $success ? self::STATUS_SUCCESS : self::STATUS_ERROR;

        // set values
        if ($success) {

            $this->body = $this->response->json();
            $this->error = false;
        }
        else {
            $this->body = [];
            $this->error = $this->response->json();
        }
    }

    public function isSuccess()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isError()
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function isDirty()
    {
        return $this->status === self::STATUS_DIRTY;
    }

    public function isReady()
    {
        return $this->status === self::STATUS_READY;
    }






    // ArrayAccess

    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->body[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->body[] = $value;
        } else {
            $this->body[$offset] = $value;
        }
        $this->status = self::STATUS_DIRTY;
    }

    public function offsetUnset($offset)
    {
        unset($this->body[$offset]);
        $this->status = self::STATUS_DIRTY;
    }

    

    // Countable

    public function count()
    {
        return count($this->body);
    }

    

    // IteratorAggregate

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->body);
    }

}