<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\ClientInterface;

/**
 * Defines an object as being reusable. We can reset and init the object without creating a new instance.
 */
interface ReusableObjectInterface
{
    /**
     * Resets the current instance to its initial state.
     */
    public function reset();

    /**
     * Single entry point to initialize the current instance
     * and its properties.
     *
     * @param array $data
     */
    public function init(array $data);

    /**
     * Get current client instance, either of Application or Client class.
     *
     * @param boolean $required
     *
     * @return ClientInterface
     */
    public function getClient($required = false);

    /**
     * Set the client which the object will use to make API requests.
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client);
}
