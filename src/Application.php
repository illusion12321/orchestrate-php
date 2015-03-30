<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\Collection;

/**
 * Resource-like interface for Orchestrate API.
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractClientBase
{

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        return $this->getHttpClient(true)->ping();
    }

    /**
     *
     * @return Collection
     */
    public function collection($name)
    {
        return (new Collection())
            ->setCollection($name)
            ->setHttpClient($this->getHttpClient(true));
    }
}
