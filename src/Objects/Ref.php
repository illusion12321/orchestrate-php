<?php
namespace andrefelipe\Orchestrate\Objects;

class Ref extends KeyValue
{
    /**
     * @var boolean
     */
    private $_tombstone = false;    

    /**
     * @return boolean
     */
    public function isTombstone()
    {
        return $this->_tombstone;
    }
    
    /**
     * @var int
     */
    private $_reftime = 0;

    /**
     * @return int
     */
    public function getRefTime()
    {
        return $this->_reftime;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if ($this->_reftime)
            $result['reftime'] = $this->_reftime;

        if ($this->_tombstone)
            $result['path']['tombstone'] = true;
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->_reftime = 0;
        $this->_tombstone = false;
    }

    public function init(array $values)
    {
        parent::init($values);

        if (isset($values['reftime'])) {
            $this->_reftime = (int) $values['reftime'];
        }

        if (isset($values['path']['tombstone'])) {
            $this->_tombstone = (bool) $values['path']['tombstone'];
        }

        return $this;
    }
}
