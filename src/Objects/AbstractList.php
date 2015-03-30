<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ObjectArray;
use andrefelipe\Orchestrate\Common\ToJsonInterface;
use andrefelipe\Orchestrate\HttpClientInterface;
use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use JmesPath\Env as JmesPath;

abstract class AbstractList extends AbstractResponse implements
\ArrayAccess,
\IteratorAggregate,
\Countable,
\Serializable,
ListInterface,
ToJsonInterface,
ReusableObjectInterface
{
    use CollectionTrait;

    /**
     * @var ObjectArray
     */
    protected $_results = null;

    /**
     * @var int
     */
    protected $_totalCount = null;

    /**
     * @var string
     */
    protected $_nextUrl = '';

    /**
     * @var string
     */
    protected $_prevUrl = '';

    /**
     * @param string $collection
     */
    public function __construct($collection = null)
    {
        $this->setCollection($collection);
    }

    /**
     * Set the client which this object, and all of its children,
     * will use to make Orchestrate API requests.
     *
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        parent::setHttpClient($httpClient);

        foreach ($this->getResults() as $item) {
            if ($item instanceof ConnectionInterface) {
                $item->setHttpClient($httpClient);
            }
        }
        return $this;
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

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_totalCount = null;
        $this->_nextUrl = '';
        $this->_prevUrl = '';
        $this->_results = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {

                if ($key === 'total_count') {
                    $this->_totalCount = (int) $value;

                } elseif ($key === 'prev') {
                    $this->_prevUrl = $value;

                } elseif ($key === 'next') {
                    $this->_nextUrl = $value;

                } elseif ($key === 'results') {
                    $this->_results = new ObjectArray(array_map(
                        [$this, 'createInstance'],
                        $value
                    ));

                    // set Collection name if not already
                    if (!$this->_collection && isset($this->_results[0])
                        && method_exists($this->_results[0], 'getCollection')
                    ) {
                        $this->setCollection($this->_results[0]->getCollection());
                    }
                }
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = [
            'kind' => 'list',
            'count' => count($this),
            'results' => $this->getResults()->toArray(),
        ];

        if ($this->_totalCount !== null) {
            $data['total_count'] = $this->_totalCount;
        }
        if ($this->_nextUrl) {
            $data['next'] = $this->_nextUrl;
        }
        if ($this->_prevUrl) {
            $data['prev'] = $this->_prevUrl;
        }

        return $data;
    }

    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    public function extract($expression)
    {
        $result = JmesPath::search($expression, $this->toArray());
        return is_array($result) ? new ObjectArray($result) : $result;
    }

    public function extractValues($expression)
    {
        $result = JmesPath::search($expression, $this->getValues()->toArray());
        return is_array($result) ? new ObjectArray($result) : $result;
    }

    /**
     * @return ObjectArray
     */
    public function getValues()
    {
        $values = [];
        foreach ($this->getResults() as $item) {
            if ($item instanceof ValueInterface) {
                $values[] = $item->getValue();
            }
        }
        return new ObjectArray($values);
    }

    /**
     * @return ObjectArray
     */
    public function getResults()
    {
        if (!$this->_results) {
            $this->_results = new ObjectArray();
        }
        return $this->_results;
    }

    /**
     * @return self
     */
    public function mergeResults(ListInterface $list)
    {
        $this->getResults()->merge($list->getResults());
        return $this;
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * @param string $serialized
     *
     * @throws \InvalidArgumentException
     */
    public function unserialize($serialized)
    {
        if (is_string($serialized)) {
            $data = unserialize($serialized);

            if (is_array($data)) {

                $this->init($data);
                return;
            }
        }
        throw new \InvalidArgumentException('Invalid serialized data type.');
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

    /**
     * @return boolean Success of operation.
     */
    public function nextPage()
    {
        return $this->getUrl($this->_nextUrl);
    }

    /**
     * @return boolean Success of operation.
     */
    public function prevPage()
    {
        return $this->getUrl($this->_prevUrl);
    }

    /**
     * Request and parse the results.
     */
    protected function request($method, $url = null, array $options = [])
    {
        // request
        parent::request($method, $url, $options);

        if ($this->isSuccess()) {

            // reset local properties
            $this->_results = null;
            $this->_nextUrl = '';
            $this->_prevUrl = '';

            // set properties
            $body = $this->getBody();

            if (!empty($body['results'])) {
                $this->_results = new ObjectArray(array_map(
                    [$this, 'createInstance'],
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
     *
     * @param string $url Orchestrate URL to request, usually a page URL.
     *
     * @return boolean Success of operation.
     */
    protected function getUrl($url)
    {
        // load next set of values
        if ($url) {

            // remove version and slashes at the beginning
            $url = ltrim($url, '/' . $this->getHttpClient(true)->getApiVersion() . '/'); //TODO find a way to do without the getApiVersion

            // request
            $this->request('GET', $url);
            return $this->isSuccess();
        }

        return false;
    }
}
