<?php
namespace andrefelipe\Orchestrate\Objects\Properties;

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
    private $_application;

    /**
     * Get current Application instance.
     * 
     * @param boolean $required
     * 
     * @return Application
     */
    public function getApplication($required = false)
    {
        if ($required)
            $this->noApplicationException();

        return $this->_application;
    }

    /**
     * Set the Application which which the object will belong to. API requests
     * will use the Application HTTP client.
     * 
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->_application = $application;
        
        return $this;
    }

    /**
     * @throws \BadMethodCallException if 'application' is not set yet.
     */
    protected function noApplicationException()
    {
        if (!$this->_application) {
            throw new \BadMethodCallException('There is no application set yet. Please do so through setApplication() method.');
        }
    }
}
