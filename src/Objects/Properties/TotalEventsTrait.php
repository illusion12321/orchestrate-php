<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the total events method.
 *
 * @internal
 */
trait TotalEventsTrait
{
    /**
     * @var int
     */
    private $_totalEvents = null;

    /**
     * @return int
     */
    public function getTotalEvents()
    {
        // makes a straight Search query for no results
        $path = $this->getCollection(true);
        $parameters = [
            'query' => '@path.kind:event',
            'limit' => 0,
        ];
        $response = $this->getHttpClient(true)->request('GET', $path, ['query' => $parameters]);

        // set value if succesful
        if ($response->getStatusCode() === 200) {
            $body = $response->json();
            $this->_totalEvents = !empty($body['total_count']) ? (int) $body['total_count'] : 0;
        }
        return $this->_totalEvents;
    }
}
