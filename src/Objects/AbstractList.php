<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\CollectionTrait;
use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ToJsonInterface;

abstract class AbstractList extends AbstractResponse implements
    \ArrayAccess,
    \IteratorAggregate,
    \Countable,
    ListInterface,
    ToJsonInterface
{
    use CollectionTrait;
    
    /**
     * @var ObjectArray
     */
    private $_results;

    /**
     * @var int
     */
    private $_totalCount = null;

    /**
     * @var string
     */
    private $_nextUrl = '';

    /**
     * @var string
     */
    private $_prevUrl = '';

    /**
     * @param string $collection
     */
    public function __construct($collection)
    {
        $this->setCollection($collection);
    }

    public function offsetGet($offset)
    {
        return $this->getResults()[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->getResults()[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->getResults()[$offset] = null;
    }

    public function offsetExists($offset)
    {
        return isset($this->getResults()[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getResults());
    }

    public function count()
    {
        return count($this->getResults());
    }

    public function toArray()
    {
        $result = [
            'kind' => 'list',
            'count' => count($this->getResults()),
            'total_count' => $this->_totalCount,
            'results' => $this->getResults()->toArray(),
        ];

        if ($this->_nextUrl) {
            $result['next'] = $this->_nextUrl;
        }
        if ($this->_prevUrl) {
            $result['prev'] = $this->_prevUrl;
        }

        return $result;
    }

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    /**
     * @return array
     */
    public function getResults()
    {
        if (!$this->_results) {
            $this->_results = new ObjectArray();
        }
        return $this->_results;
    }

    public function mergeResults(ListInterface $list)
    {
        if ($list) {
            $this->getResults()->merge($list->getResults());
        }
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_totalCount;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->_nextUrl;
    }

    /**
     * @return string
     */
    public function getPrevUrl()
    {
        return $this->_prevUrl;
    }

    public function reset()
    {
        parent::reset();
        $this->_totalCount = null;
        $this->_nextUrl = '';
        $this->_prevUrl = '';
        $this->_results = null;
    }

    /**
     * @return boolean Success of operation.
     */
    public function next()
    {
        return $this->getUrl($this->_nextUrl);
    }

    /**
     * @return boolean Success of operation.
     */
    public function prev()
    {
        return $this->getUrl($this->_prevUrl);
    }

    /**
     * Request and parse the results.
     */
    protected function request($method, $url = null, array $options = [])
    {
        $this->reset();
        parent::request($method, $url, $options);

        if ($this->isSuccess()) {
            $body = $this->getBody();

            if (!empty($body['results'])) {
                $this->_results = new ObjectArray(array_map(
                    [$this, 'createChildrenClass'],
                    $body['results']
                ));
            }

            if (isset($body['total_count'])) {
                $this->_totalCount = (int) $body['total_count'];
            }

            if (!empty($body['next'])) {
                $this->_nextUrl = $body['next'];
            }

            if (!empty($body['prev'])) {
                $this->_prevUrl = $body['prev'];
            }
        }
    }

    /**
     * Helper for next/prev methods, to sanitize the URL and request.
     */
    protected function getUrl($url)
    {
        // reset object
        $this->reset();

        // load next set of values
        if ($url) {

            // remove version and slashes at the beginning
            $url = ltrim($url, '/'.$this->getApplication(true)->getApiVersion().'/');

            // request
            $this->request('GET', $url);
            return $this->isSuccess();
        }

        return false;
    }
    
    protected function createChildrenClass(array $values)
    {
        return (new KeyValue($this->getCollection()))
            ->setApplication($this->getApplication())
            ->init($values);
    }
}
