<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate as Orchestrate;
use andrefelipe\Orchestrate\Common\ObjectArray;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractList implements ApplicationInterface
{
    use Properties\AggregatesTrait;

    /**
     * If you provide any parameters if will instantiate a HTTP client on construction.
     * Otherwise it will create one when required.
     *
     * @param string $apiKey Orchestrate API key. If not set gets from env 'ORCHESTRATE_API_KEY'.
     * @param string $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param string $version Orchestrate API version. Defaults to 'v0'
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        // lazily instantiante
        if ($apiKey || $host || $version) {
            $client = Orchestrate\default_http_client($apiKey, $host, $version);
            $this->setHttpClient($client);
        }
    }

    public function ping()
    {
        return $this->getHttpClient()->request('HEAD')->getStatusCode() === 200;
    }

    /**
     * Creates a new application instance configured with the same Http client.
     * Useful to create different instances to populate with different search results.
     *
     * @return Application
     */
    public function application()
    {
        return (new Application())
            ->setHttpClient($this->getHttpClient());
    }

    public function collection($name)
    {
        return (new Collection())
            ->setCollection($name)
            ->setHttpClient($this->getHttpClient());
    }

    public function reset()
    {
        parent::reset();
        $this->_aggregates = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['aggregates'])) {
                $this->_aggregates = new ObjectArray($data['aggregates']);
            }

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;

        if ($this->_aggregates) {
            $data['aggregates'] = $this->_aggregates->toArray();
        }

        return $data;
    }

    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $parameters = [
            'query' => $query,
            'limit' => $limit,
        ];
        if (!empty($sort)) {
            $parameters['sort'] = implode(',', (array) $sort);
        }
        if (!empty($aggregate)) {
            $parameters['aggregate'] = implode(',', (array) $aggregate);
        }
        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', null, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    /**
     * Adds aggregates support.
     */
    protected function setResponseValues()
    {
        parent::setResponseValues();

        if ($this->isSuccess()) {
            $body = $this->getBody();
            if (!empty($body['aggregates'])) {
                $this->_aggregates = new ObjectArray($body['aggregates']);
            }
        }
    }

    /**
     * @param array $itemValues
     */
    protected function createInstance(array $itemValues)
    {
        if (!empty($itemValues['path']['kind'])) {
            $kind = $itemValues['path']['kind'];

            if ($kind === 'item') {
                $item = (new KeyValue())->init($itemValues);

            } elseif ($kind === 'event') {
                $item = (new Event())->init($itemValues);

            } elseif ($kind === 'relationship') {
                $item = (new Relationship())->init($itemValues);

            } else {
                return null;
            }

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
