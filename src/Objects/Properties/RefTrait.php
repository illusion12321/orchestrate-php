<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

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
    private $_ref = null;

    /**
     * @return string
     */
    public function getRef($required = false)
    {
        if ($required) {
            $this->noRefException();
        }
        
        return $this->_ref;
    }

    /**
     * @param string $ref
     * 
     * @return self
     */
    public function setRef($ref)
    {
        $this->_ref = (string) $ref;

        return $this;
    }

    private function setRefFromETag()
    {
        if ($etag = $this->getResponse()->getHeader('ETag')) {
            $this->_ref = trim($etag, '"');
        }
    }

    /**
     * @throws \BadMethodCallException if 'ref' is not set yet.
     */
    protected function noRefException()
    {
        if (!$this->_ref) {
            throw new \BadMethodCallException('There is no ref set yet. Please do so through setRef() method.');
        }
    }
}
