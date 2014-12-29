<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;

// use andrefelipe\Orchestrate\Client;
// use andrefelipe\Orchestrate\Response;
use GuzzleHttp\Message\ResponseInterface;


// TODO method to move object to another collection
// TODO method to move object to another application


class KeyValue extends AbstractObject
{
        
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $ref;
       



    // decidir se o ref é passado aqui ou nos métodos, ver como vai lidar com o put if match


    public function __construct(Application $application, $collection, $key, $ref=null)
    {
        parent::__construct($application, $collection);
        $this->key = $key;
        $this->ref = $ref;
    }



    public function getKey()
    {
        return $this->key;
    }

    public function getRef()
    {
        return $this->ref;
    }




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

        // TODO if match
        // if ($ref)
        //     $path .= '/refs/'.$ref;

        // request
        $this->request('PUT', $path, [ 'json' => $value ]);

        // set ref
        $this->ref = $ref;
        $this->setRefFromETag();

        // set body as input value if success
        if ($this->isSuccess()) {
            $this->body = $value;
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

    


}