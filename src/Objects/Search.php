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

        // set values
        if ($this->isSuccess()) {
                
            $this->results = (array) $this->body['results'];
            $this->totalCount = (int) $this->body['total_count'];
            $this->nextUrl = !empty($this->body['next']) ? $this->body['next'] : '';
            $this->prevUrl = !empty($this->body['prev']) ? $this->body['prev'] : '';

        } else {

            $this->totalCount = 0;
            $this->nextUrl = '';
            $this->prevUrl = '';
            $this->results = [];
        }

        return $this;
    }

    



}