<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Collection;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractClient
{
    /**
     *
     * @return Collection
     */
    public function collection($name)
    {
        return (new Collection())->setCollection($name)->setClient($this);
    }
}
