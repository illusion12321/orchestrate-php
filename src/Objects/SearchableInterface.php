<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Defines an object as being searchable. Object can be part of search results.
 */
interface SearchableInterface
{
    /**
     * @return float
     */
    public function getScore();

    /**
     * @return float
     */
    public function getDistance();
}
