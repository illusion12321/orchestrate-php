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
        $this->setHttpClient(new HttpClient($apiKey, $host));
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->getHttpClient(true)->ping();
    }

    /**
     *
     * @return Collection
     */
    public function collection($name)
    {
        return (new Collection())
            ->setCollection($name)
            ->setHttpClient($this->getHttpClient(true));
    }
}
