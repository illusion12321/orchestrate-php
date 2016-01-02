<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Define the Application minimum required interface.
 */
interface ApplicationInterface extends ListInterface, ObjectInterface
{
    const KIND = 'application';

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping();

    /**
     * @param string $name Collection name
     *
     * @return Collection
     */
    public function collection($name);

    /**
     * Search!
     *
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-root
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0);

    /**
     * @return ObjectArray
     */
    public function getAggregates();
}
