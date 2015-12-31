<?php
namespace andrefelipe\Orchestrate\Objects;

use GuzzleHttp\ClientInterface;

/**
 *
 */
interface ConnectionInterface
{
    /**
     * Gets the current object's HTTP client. If not set yet, it will create
     * a pre-configured Guzzle Client with the default settings.
     *
     * @return ClientInterface
     */
    public function getHttpClient();

    /**
     * Sets the HTTP client which the object will use to make API requests.
     *
     * @param ClientInterface $httpClient
     *
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient);
}
