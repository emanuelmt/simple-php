<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Exception;

class ErrorRegister {

    public function __construct($app) {
        $this->app = $app;
    }

    public static function initialize() {
        $GLOBALS['Errors'] = [];
    }

    public static function register(Error $error, $breakScript = false) {
        $GLOBALS['Errors'][] = $error;
        if ($breakScript) {
            return self::render($breakScript);
        }
    }

    public static function getErrors() {
        return $GLOBALS['Errors'] ?? [];
    }

    public static function render($breakScript = false) {
        if (!getenv('DEBUG')) {
            $serverRequestCreator = \Slim\Factory\ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
            $response = $GLOBALS['App']->errorsHandler->__invoke($request, null, $breakScript);

            if ($breakScript) {
                $responseEmitter = new \Slim\ResponseEmitter();
                $responseEmitter->emit($response);
            }
            exit();
        } else {
            if ($GLOBALS['App']->errorsRegister) {
                var_dump($GLOBALS['App']->errorsRegister->getErrors());
                if ($breakScript) {
                    exit();
                }
            }
        }
    }

}
