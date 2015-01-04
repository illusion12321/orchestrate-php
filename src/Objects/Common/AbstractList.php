<?php
namespace andrefelipe\Orchestrate\Objects\Common;


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
            'count' => count($this->data),
            'total_count' => $this->totalCount,
            'results' => [],
        ];

        foreach ($this->data as $object) {
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
            $url = ltrim($url, '/'.$this->application->getApiVersion().'/');

            // request
            $this->request('GET', $url);
        }

        return $this;
    }



    protected function request($method, $url = null, array $options = [])
    {
        $this->reset();
        parent::request($method, $url, $options);

        if ($this->isSuccess()) {
            
            $this->data = !empty($this->body['results'])
                ? array_map([$this, 'createKeyValue'], $this->body['results'])
                : [];
            $this->count = !empty($this->body['count']) ? (int) $this->body['count'] : 0;
            $this->totalCount = !empty($this->body['total_count']) ? (int) $this->body['total_count'] : 0;
            $this->nextUrl = !empty($this->body['next']) ? $this->body['next'] : '';
            $this->prevUrl = !empty($this->body['prev']) ? $this->body['prev'] : '';

        }
    }


    private function createKeyValue(array $values)
    {
        return (new KeyValue($this->application, $this->collection))->init($values);
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