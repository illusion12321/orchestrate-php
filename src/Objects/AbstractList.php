<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


class AbstractList extends AbstractObject
{
        

    /**
     * @var int
     */
    protected $totalCount = 0;

    /**
     * @var string
     */
    protected $nextUrl = '';

    /**
     * @var string
     */
    protected $prevUrl = '';

    /**
     * @var array
     */
    protected $results = [];



    // no need for constructor


    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this);
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @return string
     */
    public function getPrevUrl()
    {
        return $this->prevUrl;
    }



    public function next()
    {
        $nextUrl = $this->nextUrl;

        $this->response = null;
        $this->status = '';
        $this->statusCode = 0;
        $this->statusMessage = '';
        $this->body = [];

        $this->totalCount = 0;
        $this->nextUrl = '';
        $this->prevUrl = '';
        $this->results = [];


        if ($nextUrl) {

            // remove version and slashes at the beginning
            $url = ltrim($nextUrl, '/'.$this->application->getApiVersion().'/');

            // request
            $this->request('GET', $url);

            // set values
            if ($this->isSuccess()) {
                    
                $this->results = (array) $this->body['results'];
                $this->totalCount = !empty($this->body['total_count']) ? (int) $this->body['total_count'] : 0;
                $this->nextUrl = !empty($this->body['next']) ? $this->body['next'] : '';
                $this->prevUrl = !empty($this->body['prev']) ? $this->body['prev'] : '';
            }
        }       

        return $this;
    }








    // ArrayAccess

    public function offsetExists($offset)
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->results[] = $value;
        } else {
            $this->results[$offset] = $value;
        }
        $this->hasChanged = true;
    }

    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);
        $this->hasChanged = true;
    }


    // Countable

    public function count()
    {
        return count($this->results);
    }


    // IteratorAggregate

    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }




}