<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

/**
 * Trait that implements the total items method.
 *
 * @internal
 */
trait TotalItemsTrait
{
    /**
     * @var int
     */
    private $_totalItems = null;

    /**
     * @return int
     */
    public function getTotalItems()
    {
        // makes a straight Search query for no results
        $path = $this->getCollection(true);
        $parameters = [
            'query' => '@path.kind:item',
            'limit' => 0,
        ];
        $response = $this->getHttpClient(true)->request('GET', $path, ['query' => $parameters]);

        // set value if succesful
        if ($response->getStatusCode() === 200) {
            $body = $response->json();
            $this->_totalItems = !empty($body['total_count']) ? (int) $body['total_count'] : 0;
        }
        return $this->_totalItems;
    }
}
