<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Application;


/**
 * Trait that implements the Application methods
 */
trait ApplicationTrait
{

    /**
     * @var Application
     */
    protected $application;
    

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    
    
}