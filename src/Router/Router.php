<?php

namespace SimplePHP\Router;
use Slim\Factory\AppFactory;

class Router{
    
    public $Router;
    
    public function __construct() {
        $this->Router = AppFactory::create();
    }
}