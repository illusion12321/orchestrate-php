<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Tombstone methods.
 * 
 * @internal
 */
trait TombstoneTrait
{

    /**
     * @var boolean
     */
    protected $tombstone = false;
    

    /**
     * @return boolean
     */
    public function isTombstone()
    {
        return $this->tombstone;
    }
    
}