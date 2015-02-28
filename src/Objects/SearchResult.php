<?php
namespace andrefelipe\Orchestrate\Objects;

class SearchResult extends KeyValue
{
    /**
     * @var float
     */
    private $_score = 0;

    /**
     * @var float
     */
    private $_distance = 0;

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->_score;
    }    

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->_distance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if ($this->_score)
            $result['score'] = $this->_score;

        if ($this->_distance)
            $result['distance'] = $this->_distance;
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->_score = 0;
        $this->_distance = 0;
    }

    public function init(array $values)
    {
        parent::init($values);

        if (isset($values['score'])) {
            $this->_score = (float) $values['score'];
        }

        if (isset($values['distance'])) {
            $this->_distance = (float) $values['distance'];
        }

        return $this;
    }
}
