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
    protected $application;
    

    /**
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
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        
        return $this;
    }

    
    
}