<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\AbstractConnection;

/**
 * Provides the entry point for Orchestrate clients. Instantiates a default HTTP client on construction.
 */
abstract class AbstractHttpConnection extends AbstractConnection
{
    /**
     * @param string $apiKey
     * @param string $host
     * @param string $version
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        $base_url = $host ? trim($host, '/') : HttpClient::DEFAULT_HOST;
        $base_url .= '/' . ($version ? $version : HttpClient::DEFAULT_VERSION) . '/';

        $client = new HttpClient(['base_url' => $base_url]);

        if ($apiKey !== null) {
            $client->setApiKey($apiKey);
        }

        $this->setHttpClient($client);
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->getHttpClient(true)->ping();
    }
}
