<?php
namespace andrefelipe\Orchestrate\Objects;

class Search extends AbstractList
{
    

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     * @return Search self
     */
    public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)
    {
        // required values
        $this->noCollectionException();

        // define request options
        $parameters = [
            'query' => $query,
            'limit'=> $limit,
        ];

        if (!empty($sort)) {
            $parameters['sort'] = (array) implode(',', $sort);
        }

        if (!empty($aggregate)) {
            $parameters['aggregate'] = (array) implode(',', $aggregate);
        }

        if ($offset) {
            $parameters['offset'] = $offset;
        }
        
        // request
        $this->request('GET', $this->collection, ['query' => $parameters]);

        return $this;
    }
   



}