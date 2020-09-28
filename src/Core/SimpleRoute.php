<?php

namespace SimplePHP\Core;

abstract class SimpleRoute {

    protected $contentType = 'application/json';

    /**
     * Procedure to define the routes of the application
     * 
     * @see getAttributes()
     * @param Slim\App instance.
     */
    protected abstract function routes(\Slim\App $router);

    public function __invoke(\SimplePHP\Application $application, \Slim\App $router) {
        $application->setContentType($this->contentType);
        $this->routes($router);
    }

}
