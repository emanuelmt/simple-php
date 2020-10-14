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

    public static function register(Error $error, $breakScript = false, $response = null) {
        $GLOBALS['Errors'][] = $error;
        if ($breakScript && $response) {
            return self::render($breakScript, $response);
        }
    }

    public static function getErrors() {
        return $GLOBALS['Errors'] ?? [];
    }

    public static function render($response = null, $breakScript = false) {
        if (!getenv('DEBUG')) {

            if ($response) {
                $responseBody = $response->getBody();

                if ("$responseBody" == '') {
                    $jsonBody = new \stdClass();
                } else {
                    $jsonBody = json_decode($responseBody);
                }
                if (json_last_error() == JSON_ERROR_NONE) {
                    $errorRenderer = new \SimplePHP\Exception\ErrorRenderer();
                    $errors = json_decode($errorRenderer());
                    if (isset($jsonBody->errors) && $errors->errors) {
                        foreach ($errors->errors as $erro) {
                            $jsonBody->errors[] = $erro;
                        }
                    } else if ($errors->errors) {
                        $jsonBody->errors = $errors->errors;
                    }
                    $response = $errorRenderer->renderErrors(new \GuzzleHttp\Psr7\Response(), json_encode($jsonBody));
                }
                if ($breakScript) {
                    $responseEmitter = new \Slim\ResponseEmitter();
                    $responseEmitter->emit($response);
                    exit();
                } else {
                    return $response;
                }
            }
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
