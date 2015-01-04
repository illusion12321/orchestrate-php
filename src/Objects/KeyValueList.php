<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


class KeyValueList extends AbstractList
{
    

    /**
     * @param int $limit
     * @param array $range
     * @return KeyValueList self
     */
    public function listCollection($limit=10, array $range=null)
    {
        // required values
        $this->noCollectionException();

        // define request options
        $parameters = ['limit' => $limit];

        if ($range) {
            if (isset($range['start']))
                $parameters['startKey'] = $range['start'];

            if (isset($range['after']))
                $parameters['afterKey'] = $range['after'];

            if (isset($range['before']))
                $parameters['beforeKey'] = $range['before'];

            if (isset($range['end']))
                $parameters['endKey'] = $range['end'];
        }        

        // request
        $this->request('GET', $this->collection, ['query' => $parameters]);
        
        return $this;
    }
   



}