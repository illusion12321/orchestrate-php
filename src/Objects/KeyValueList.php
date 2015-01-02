<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


// TODO dynamic pagination (iterators, etc)
// nextPage, prevPage ...


class KeyValueList extends AbstractList
{



    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search self
     */
    public function getList($limit=10, $startKey='', $afterKey='', $beforeKey='', $endKey='')
    {
        // define request options
        $parameters = ['limit' => $limit];

        if ($startKey)
            $parameters['startKey'] = $startKey;
       
        if ($afterKey)
            $parameters['afterKey'] = $afterKey;

        if ($beforeKey)
            $parameters['beforeKey'] = $beforeKey;

        if ($endKey)
            $parameters['endKey'] = $endKey;

        // request
        $this->request('GET', $this->collection, ['query' => $parameters]);
        
        return $this;
    }


    


    



}