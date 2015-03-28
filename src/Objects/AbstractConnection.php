<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\HttpClientInterface;

/**
 *
 */
abstract class AbstractConnection
{
    /**
     * @var HttpClientInterface
     */
    private $_httpClient;

    /**
     * Get current Http client instance.
     *
     * @param boolean $required
     *
     * @return HttpClientInterface
     */
    public function getHttpClient($required = false)
    {
        if ($required) {
            $this->noHttpClientException();
        }

        return $this->_httpClient;
    }

    /**
     * Set the Http client which the object will use to make API requests.
     *
     * @param HttpClientInterface $httpClient
     *
     * @return HttpClientInterface self
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if the http client is not set yet.
     */
    protected function noHttpClientException()
    {
        if (!$this->_httpClient) {
            throw new \BadMethodCallException('There is no Http client set yet. Please do so through setHttpClient() method.');
        }
    }
}
