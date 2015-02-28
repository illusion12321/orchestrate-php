<?php
namespace andrefelipe\Orchestrate\Objects;

class KeyValues extends AbstractList
{
    /**
     * @param int $limit
     * @param array $range
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection($limit = 10, array $range = null)
    {
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
        $this->request('GET', $this->getCollection(true), ['query' => $parameters]);
        
        return $this->isSuccess();
    }

    // if ($this->_totalCount === null) {

    //     // get from Orchestrate
    //     $response = $this->getApplication(true)
    //         ->request('GET', $this->getCollection(true), ['query' => ['limit' => 1]]);

    //     if ($response) {
    //         $body = $response->json();
    //         print_r($body);
            
    //         if (!empty($body['total_count'])) {
    //             $this->_totalCount = (int) $body['total_count'];
    //         }
    //     }                
    // }
}
