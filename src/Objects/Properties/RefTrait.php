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
     * @param boolean $required
     *
     * @return string
     * @throws \BadMethodCallException if 'ref' is required but not set yet.
     */
    public function getRef($required = false)
    {
        if ($required && !$this->_ref) {
            throw new \BadMethodCallException('There is no ref set yet. Do so through setRef() method.');
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

    protected function setRefFromETag()
    {
        $etag = $this->getResponse()->getHeader('ETag');
        $this->_ref = !empty($etag) ? trim($etag[0], '"') : null;
    }
}
