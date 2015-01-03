<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


// TODO dynamic pagination (iterators, etc)
// nextPage, prevPage ...


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
        // define request options
        $options = [
            'query' => [
                'query' => $query,
                'sort' => $sort,
                'limit'=> $limit,
                'offset' => $offset,
            ]
        ];
        
        // request
        $this->request('GET', $this->collection, $options);

        return $this;
    }

    



}