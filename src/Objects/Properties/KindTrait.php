<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Kind methods for Graphs.
 * 
 * @internal
 */
trait KindTrait
{
    /**
     * @var array
     */
    private $_kind = null;
    
    /**
     * @param boolean $required
     * 
     * @return int
     */
    public function getKind($required = false)
    {
        if ($required)
            $this->noKindException();

        return $this->_kind;
    }

    /**
     * @param string|array $kind
     * 
     * @return self
     */
    public function setKind($kind)
    {
        $this->_kind = (array) $kind;

        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'kind' is not set yet.
     */
    protected function noKindException()
    {
        if (empty($this->_kind)) {
            throw new \BadMethodCallException('There is no kind set yet. Please do so through setKind() method.');
        }
    }
}
