<?php
namespace andrefelipe\Orchestrate\Objects\Common;

/**
 * Trait that implements the Type methods.
 * 
 * @internal
 */
trait TypeTrait
{

    /**
     * @var string
     */
    protected $type = null;
    

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @throws \BadMethodCallException if 'type' is not set yet.
     */
    protected function noTypeException()
    {
        if (!$this->type) {
            throw new \BadMethodCallException('There is no type set yet. Please do so through setType() method.');
        }
    }
    
}