<?php
namespace andrefelipe\Orchestrate\Objects;

class Search extends AbstractList
{
    

    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search self
     */
    public function search($query, $sort='', $limit=10, $offset=0)
    {
        // required values
        $this->noCollectionException();

        // define request options
        $parameters = [
            'query' => $query,
            'limit'=> $limit,
        ];

        if ($sort)
            $parameters['sort'] = $sort;

        if ($offset)
            $parameters['offset'] = $offset;
        
        // request
        $this->request('GET', $this->collection, ['query' => $parameters]);

        return $this;
    }
   



}