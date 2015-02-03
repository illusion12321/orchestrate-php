<?php
namespace andrefelipe\Orchestrate\Objects;

class Ref extends KeyValue
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
    
    /**
     * @var int
     */
    protected $reftime = 0;

    /**
     * @return int
     */
    public function getRefTime()
    {
        return $this->reftime;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if ($this->reftime)
            $result['reftime'] = $this->reftime;

        if ($this->isTombstone())
            $result['path']['tombstone'] = true;
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->reftime = 0;
        $this->tombstone = false;
    }

    public function init(array $values)
    {
        parent::init($values);

        if (!empty($values['reftime'])) {
            $this->reftime = (int) $values['reftime'];
        }

        if (!empty($values['path']['tombstone'])) {
            $this->tombstone = true;
        }

        return $this;
    }
}
