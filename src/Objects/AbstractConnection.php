<?php
namespace andrefelipe\Orchestrate\Objects;

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
     * @param boolean $required
     *
     * @return ClientInterface
     * @throws \BadMethodCallException if the http client is required but not set yet.
     */
    public function getHttpClient($required = false)
    {
        if ($required && !$this->_httpClient) {
            throw new \BadMethodCallException('There is no HTTP client set yet. Do so through setHttpClient() method.');
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
