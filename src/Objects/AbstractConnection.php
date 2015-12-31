<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate as Orchestrate;
use GuzzleHttp\ClientInterface;

/**
 * Provides the bare basis, a connection to a HTTP client.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var ClientInterface
     */
    private $_httpClient;

    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = Orchestrate\default_http_client();
        }

        return $this->_httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        return $this;
    }
}
