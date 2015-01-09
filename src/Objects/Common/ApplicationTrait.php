<?php
namespace andrefelipe\Orchestrate\Objects\Common;

use andrefelipe\Orchestrate\Application;


/**
 * Trait that implements the Application methods.
 * 
 * @internal
 */
trait ApplicationTrait
{

    /**
     * @var Application 
     */
    private $application;

    /**
     * Get current Application instance. If not set, will automatically try
     * to get the last created instance with Application::getCurrent()
     * 
     * @return Application
     */
    public function getApplication()
    {
        if (!$this->application) {
            $this->application = Application::getCurrent();
        }

        return $this->application;
    }

    /**
     * Set the Application which which the object will belong to. API requests
     * will use the Application HTTP client.
     * 
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        
        return $this;
    }

    
    
}