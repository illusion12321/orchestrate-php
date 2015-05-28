<?php
namespace andrefelipe\Orchestrate\Objects;

use GuzzleHttp\ClientInterface;

/**
 * Provides the bare basis, a connection to a HTTP service.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @param boolean $required
     *
     * @return ClientInterface
     */
    private $_httpClient;

    public function getHttpClient($required = false)
    {
        if ($required) {
            $this->noHttpClientException();
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

    /**
     * @throws \BadMethodCallException if the http client is not set yet.
     */
    private function noHttpClientException()
    {
        if (!$this->_httpClient) {
            throw new \BadMethodCallException('There is no HTTP client set yet. Please do so through setHttpClient() method.');
        }
    }
}
