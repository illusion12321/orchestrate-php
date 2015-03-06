<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Collection;

/**
 * 
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractClient
{
    public function collection($name)
    {
        return new Collection($this, $name);
    }
}
