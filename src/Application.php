<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Collection;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractClient
{
    public function collection($name)
    {
        return (new Collection($name))->setClient($this);
    }
}
