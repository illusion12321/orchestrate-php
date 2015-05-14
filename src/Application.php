<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\AbstractConnection;
use andrefelipe\Orchestrate\Objects\Collection;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Resource-like interface for Orchestrate API.
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractConnection
{
    /**
     * Instantiates a default HTTP client on construction.
     *
     * @param string $apiKey Orchestrate API key. If not set gets from env 'ORCHESTRATE_API_KEY'.
     * @param string $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param string $version Orchestrate API version. Defaults to 'v0'
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        $config = default_http_config($apiKey, $host, $version);
        $this->setHttpClient(new GuzzleClient($config));
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->getHttpClient(true)->request('HEAD')->getStatusCode() === 200;
    }

    /**
     * @return Collection
     */
    public function collection($name)
    {
        return (new Collection())
            ->setCollection($name)
            ->setHttpClient($this->getHttpClient(true));
    }
}
