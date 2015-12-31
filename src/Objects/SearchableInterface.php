<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * Defines an object as being searchable. Object acn be part of search results.
 */
interface SearchableInterface
{
    /**
     * @return float
     */
    public function getScore();
}
