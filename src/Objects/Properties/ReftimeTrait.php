<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the Reftime methods.
 * 
 * @internal
 */
trait ReftimeTrait
{
    /**
     * @var int
     */
    private $_reftime = null;
    
   /**
     * @return int
     */
    public function getReftime()
    {
        return $this->_reftime;
    }
}
