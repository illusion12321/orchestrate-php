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
     * @var string
     */
    protected static $itemKind = 'item';

    /**
     * @var string
     */
    protected static $defaultItemClass = '\andrefelipe\Orchestrate\Objects\KeyValue';

    /**
     * @var string
     */
    protected static $minimumItemInterface = '\andrefelipe\Orchestrate\Objects\KeyValueInterface';

    /**
     * @var string
     */
    protected static $defaultEventClass = '\andrefelipe\Orchestrate\Objects\Event';

    /**
     * @var string
     */
    protected static $minimumEventInterface = '\andrefelipe\Orchestrate\Objects\EventInterface';

    /**
     * @var \ReflectionClass
     */
    private $_itemClass;

    /**
     * @var \ReflectionClass
     */
    private $_eventClass;

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
            if ($item instanceof AbstractConnection) {
                //TODO change to method_exists?
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
        $result = [
            'kind' => 'list',
            'count' => count($this),
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
        $data = $this->toArray();
        $data['itemClass'] = $this->getItemClass()->name;
        $data['eventClass'] = $this->getEventClass()->name;
        // TODO check if these vars will turn to private after all, if not add the other values here
        // $data['defaultItemClass'] = static::$defaultItemClass;
        // $data['minimumItemInterface'] = static::$minimumItemInterface;
        // $data['defaultEventClass'] = static::$defaultEventClass;
        // $data['minimumEventInterface'] = static::$minimumEventInterface;

        return serialize($data);
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

                if (!empty($data['itemClass'])) {
                    $this->setItemClass($data['itemClass']);
                }
                if (!empty($data['eventClass'])) {
                    $this->setEventClass($data['eventClass']);
                }

                // TODO same above
                // if (!empty($data['defaultChildClass'])) {
                //     static::$defaultChildClass = $data['defaultChildClass'];
                // }
                // if (!empty($data['defaultChildClass'])) {
                //     static::$minimumChildInterface = $data['minimumChildInterface'];
                // }

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
        if ($this->_totalCount === null) {

            // makes a straight Search query for no results
            $path = $this->getCollection(true);
            $parameters = [
                'query' => '@path.kind:(' . static::$itemKind . ')',
                'limit' => 0,
            ];
            $response = $this->getHttpClient(true)->request('GET', $path, ['query' => $parameters]);

            // set value if succesful
            if ($response->getStatusCode() === 200) {
                $body = $response->json();
                $this->_totalCount = !empty($body['total_count']) ? (int) $body['total_count'] : 0;
            }
        }
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
            $this->_totalCount = null;
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
     */
    protected function getUrl($url)
    {
        // load next set of values
        if ($url) {

            // remove version and slashes at the beginning
            $url = ltrim($url, '/' . $this->getHttpClient(true)->getApiVersion() . '/');

            // request
            $this->request('GET', $url);
            return $this->isSuccess();
        }

        return false;
    }

    /**
     * Get the ReflectionClass that is being used to instantiate this list's items (KeyValue).
     *
     * @return \ReflectionClass
     */
    public function getItemClass()
    {
        if (!isset($this->_itemClass)) {
            $this->_itemClass = new \ReflectionClass(static::$defaultItemClass);

            if (!$this->_itemClass->implementsInterface(static::$minimumItemInterface)) {
                throw new \RuntimeException('Item classes must implement ' . static::$minimumItemInterface);
            }
        }
        return $this->_itemClass;
    }

    /**
     * Set which class should be used to instantiate this list's items (KeyValue).
     *
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     */
    public function setItemClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_itemClass = $class;
        } else {
            $this->_itemClass = new \ReflectionClass($class);
        }

        if (!$this->_itemClass->implementsInterface(static::$minimumItemInterface)) {
            throw new \RuntimeException('Item classes must implement ' . static::$minimumItemInterface);
        }

        return $this;
    }

    /**
     * Get the ReflectionClass that is being used to instantiate this list's events.
     *
     * @return \ReflectionClass
     */
    public function getEventClass()
    {
        if (!isset($this->_eventClass)) {
            $this->_eventClass = new \ReflectionClass(static::$defaultEventClass);

            if (!$this->_eventClass->implementsInterface(static::$minimumEventInterface)) {
                throw new \RuntimeException('Event classes must implement ' . static::$minimumEventInterface);
            }
        }
        return $this->_eventClass;
    }

    /**
     * Set which class should be used to instantiate this list's events.
     *
     * @param string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     */
    public function setEventClass($class)
    {
        if ($class instanceof \ReflectionClass) {
            $this->_eventClass = $class;
        } else {
            $this->_eventClass = new \ReflectionClass($class);
        }

        if (!$this->_eventClass->implementsInterface(static::$minimumEventInterface)) {
            throw new \RuntimeException('Event classes must implement ' . static::$minimumEventInterface);
        }

        return $this;
    }

    /**
     *
     * @param array $itemValues
     */
    protected function createInstance(array $itemValues)
    {
        if (!empty($itemValues['kind'])) {

            if ($kind === 'item') {
                $class = $this->getItemClass();

            } else if ($kind === 'event') {
                $class = $this->getEventClass();

            } else {
                return null;
            }

            $item = $class->newInstance()->init($itemValues);

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
