<?php
namespace andrefelipe\Orchestrate\Objects;

use GuzzleHttp\ClientInterface;

/**
 *
 */
interface ConnectionInterface
{
    /**
     * Get current Http client instance.
     *
     * @param boolean $required
     *
     * @return ClientInterface
     */
    public function getHttpClient($required = false);

    /**
     * Set the Http client which the object will use to make API requests.
     *
     * @param ClientInterface $httpClient
     *
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient);
}
