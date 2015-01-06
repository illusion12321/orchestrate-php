<?php
namespace andrefelipe\Orchestrate\Objects;

abstract class AbstractList extends AbstractObject
{
    

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var int
     */
    protected $totalCount = 0;

    /**
     * @var string
     */
    protected $nextUrl = '';

    /**
     * @var string
     */
    protected $prevUrl = '';



    // no need for constructor


    
    /**
     * @return array
     */
    public function getResults()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'kind' => 'list',
            'count' => count($this->data),
            'total_count' => $this->totalCount,
            'results' => [],
        ];

        foreach ($this->getResults() as $object) {
            $result['results'][] = $object->toArray();
        }

        if ($this->nextUrl)
            $result['next'] = $this->nextUrl;

        if ($this->prevUrl)
            $result['prev'] = $this->prevUrl;

        return $result;
    }


    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @return string
     */
    public function getPrevUrl()
    {
        return $this->prevUrl;
    }


    public function reset()
    {
        parent::reset();
        $this->count = 0;
        $this->totalCount = 0;
        $this->nextUrl = '';
        $this->prevUrl = '';
        $this->data = [];
    }




    public function next()
    {
        return $this->getUrl($this->nextUrl);
    }

    public function prev()
    {
        return $this->getUrl($this->prevUrl);
    }



    private function getUrl($url)
    {
        // reset object
        $this->reset();

        // load next set of values
        if ($url) {

            // remove version and slashes at the beginning
            $url = ltrim($url, '/'.$this->getApplication()->getApiVersion().'/');

            // request
            $this->request('GET', $url);
        }

        return $this;
    }



    protected function request($method, $url = null, array $options = [], $childrenClass='KeyValue')
    {
        $this->reset();
        parent::request($method, $url, $options);

        if ($this->isSuccess()) {
            
            if (!empty($this->body['results'])) {
                $this->data = array_map([$this, 'create'.$childrenClass], $this->body['results']);
            }

            if (!empty($this->body['count'])) {
                $this->count = (int) $this->body['count'];
            }

            if (!empty($this->body['total_count'])) {
                $this->totalCount = (int) $this->body['total_count'];
            }

            if (!empty($this->body['next'])) {
                $this->nextUrl = $this->body['next'];
            }

            if (!empty($this->body['prev'])) {
                $this->prevUrl = $this->body['prev'];
            }
        }
    }

    // This can be handled in a better way, just get it going for now

    private function createKeyValue(array $values)
    {
        return (new KeyValue($this->collection))
            ->setApplication($this->getApplication())
            ->init($values);
    }

    private function createEvent(array $values)
    {
        return (new Event($this->collection))
            ->setApplication($this->getApplication())
            ->init($values);
    }
    











    // override ArrayAccess

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('You cannot mutate a list\'s data, only it\'s children, the KeyValue objects.');
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException('You cannot mutate a list\'s data, only it\'s children, the KeyValue objects.');
    }





}