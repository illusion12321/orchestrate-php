<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\AbstractConnection;
use andrefelipe\Orchestrate\Objects\Collection;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractConnection
{
    /**
     * @param string $apiKey
     * @param string $host
     */
    public function __construct($apiKey = null, $host = null)
    {
        $this->_httpClient = new HttpClient($apiKey, $host);
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->_httpClient->ping();
    }

    /**
     *
     * @return Collection
     */
    public function collection($name)
    {
        return (new Collection())->setCollection($name)->setHttpClient($this);
    }

    // public function item($key = null, $ref = null)
    // {
    //     return $this->getChildClass()->newInstance()
    //                 ->setCollection($this->getCollection(true))
    //                 ->setKey($key)
    //                 ->setRef($ref)
    //                 ->setHttpClient($this->getHttpClient(true));
    // }

    // public function events($collection, $key = null, $type = null)
    // {
    //     return (new Events())
    //         ->setCollection($collection)
    //         ->setKey($key)
    //         ->setType($type)
    //         ->setHttpClient($this);
    // }

}
