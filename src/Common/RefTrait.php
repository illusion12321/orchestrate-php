<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait that implements the Ref methods.
 * 
 * @internal
 */
trait RefTrait
{
    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = (string) $ref;

        return $this;
    }

    protected function setRefFromETag()
    {
        if ($etag = $this->response->getHeader('ETag')) {
            $this->ref = trim($etag, '"');
        }
    }
}
