<?php
namespace andrefelipe\Orchestrate\Objects;

class SearchResult extends KeyValue
{
    /**
     * @var float
     */
    protected $score = 0;

    /**
     * @var float
     */
    protected $distance = 0;

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }    

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        if ($this->score)
            $result['score'] = $this->score;

        if ($this->distance)
            $result['distance'] = $this->distance;
        
        return $result;
    }

    public function reset()
    {
        parent::reset();
        $this->score = 0;
        $this->distance = 0;
    }

    public function init(array $values)
    {
        parent::init($values);

        if (!empty($values['score'])) {
            $this->score = (float) $values['score'];
        }

        if (!empty($values['distance'])) {
            $this->distance = (float) $values['distance'];
        }

        return $this;
    }
}
