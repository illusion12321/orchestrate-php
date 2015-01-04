<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Ref methods
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
        $this->ref = $ref;
    }


    // helper

    protected function setRefFromETag()
    {
        if ($etag = $this->response->getHeader('ETag')) {
            $this->ref = trim($etag, '"');
        }
    }

}