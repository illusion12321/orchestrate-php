<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;

// use andrefelipe\Orchestrate\Client;
// use andrefelipe\Orchestrate\Response;
use GuzzleHttp\Message\ResponseInterface;


// TODO method to move object to another collection
// TODO method to move object to another application
// TODO implement archival and tombstone properties like the ruby client

// TODO adicionar proteções se não tiver o key!


class KeyValue extends AbstractObject
{
        
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $ref = null;
    



    public function __construct(Application $application, $collection, $key=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
    }


    // TODO maybe add setKey and setCollection after all

    public function getKey()
    {
        return $this->key;
    }

    public function getRef()
    {
        return $this->ref;
    }




    /**
     * @return KeyValue self
     */
    public function get($ref=null)
    {
        // define request options
        $path = $this->collection.'/'.$this->key;

        if ($ref) {
            $path .= '/refs/'.trim($ref, '"');
        }

        // request
        $this->request('GET', $path);

        // set ref
        $this->ref = $ref;
        $this->setRefFromETag();

        return $this;
    }

    

    /**
     * @return KeyValue self
     */
    public function put(array $value=null, $ref=null)
    {
        if ($value === null) {
            if ($this->isDirty()) {
                $value = $this->body;
            } else {
                return $this;
            }
        }

        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];

        } elseif ($ref === false) {

            // set If-None-Match
            $options['headers'] = ['If-None-Match' => '"*"'];

        }

        // request
        $this->request('PUT', $path, $options);
        
        // set ref
        $this->ref = $ref;
        $this->setRefFromETag();

        // set body as input value, even if not success, we can retry
        $this->body = $value;

        return $this;
    }


    /**
     * @return KeyValue self
     */
    public function post(array $value=null)
    {
        if ($value === null) {
            if ($this->isDirty()) {
                $value = $this->body;
            } else {
                return $this;
            }
        }

        // request
        $this->request('POST', $this->collection, ['json' => $value]);
        
        // set ref
        $this->key = null;
        $this->ref = null;
        $this->setKeyRefFromLocation();

        // set body as input value, even if not success, we can retry
        $this->body = $value;

        return $this;
    }




    /**
     * @return KeyValue self
     */
    public function delete($ref=null)
    {
        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = [];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->ref;
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);
        
        // TODO confirm if the success body is array

        return $this;
    }



    /**
     * @return KeyValue self
     */
    public function purge()
    {
        // define request options
        $path = $this->collection.'/'.$this->key;
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);
        
        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->ref = null;
        }        

        return $this;
    }












    // helpers

    private function setRefFromETag()
    {
        if ($this->isSuccess()) {

            if ($etag = $this->response->getHeader('ETag')) {
                $this->ref = trim($etag, '"');
            }
        }
    }

    
    private function setKeyRefFromLocation()
    {
        // Location: /v0/collection/key/refs/ad39c0f8f807bf40

        $location = $this->response->getHeader('Location');
        if (!$location)
            $location = $this->response->getHeader('Content-Location');

        $location = explode('/', trim($location, '/'));
        if (count($location) > 4)
        {
            $this->key = $location[2];
            $this->ref = $location[4];
        }
    }





}