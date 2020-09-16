<?php

namespace SimplePHP;

class Bootstrap {

    private $appErrors = [];

    public function __construct($path) {
        $this->startEnviroments($path);
        $this->startErrorHandler();
    }

    private function startEnviroments($path = __DIR__) {
        $dotenv = \Dotenv\Dotenv::createImmutable($path);
        $dotenv->load();
    }

    private function startErrorHandler() {
        if (getenv("DEBUG")) {
            $run = new \Whoops\Run;
            $handler = new \Whoops\Handler\PrettyPageHandler;

            $handler->setPageTitle("Ooops... Ocorreu um problema inesperado!");

            if (\Whoops\Util\Misc::isAjaxRequest()) {
                $run->pushHandler(new \Whoops\Handler\JsonResponseHandler);
            } else {
                $run->pushHandler($handler);
            }
            $run->register();
        } else {
            $handler = new Exception\Handler;
            set_exception_handler([$handler, "handleException"]);
            set_error_handler([$handler, "handleError"]);
            Exception\ErrorRegister::initialize();
//            register_shutdown_function(function(){
//                Exception\ErrorRegister::render();
//            });
        }
    }

    public function getErrors() {
        return $this->appErrors;
    }

    public function setErrors(\Throwable $t) {
        return $this->appErrors;
    }

}
