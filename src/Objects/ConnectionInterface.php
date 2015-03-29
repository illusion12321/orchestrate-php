<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\HttpClientInterface;

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
     * @return HttpClientInterface
     */
    public function getHttpClient($required = false);

    /**
     * Set the Http client which the object will use to make API requests.
     *
     * @param HttpClientInterface $httpClient
     *
     * @return HttpClientInterface self
     */
    public function setHttpClient(HttpClientInterface $httpClient);
}
