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

    /**
     * @param boolean $required Will create a default Http Client if not set.
     *
     * @return ClientInterface
     */
    public function getHttpClient($required = false)
    {
        if ($required && !$this->_httpClient) {
            $this->_httpClient = Orchestrate\default_http_client();
        }

        return $this->_httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     *
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        return $this;
    }
}
