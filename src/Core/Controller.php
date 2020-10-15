<?php

namespace SimplePHP\Core;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class Controller {

    protected $variables;
    protected $errors = [];
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->view = $container->get("view");
        $this->view->getEnvironment()->addGlobal("app", (object) $GLOBALS['App']);
        $this->container = $container;
    }

    protected function validate(array &$variables, array $paramsRules) {
        $this->variables = $variables;
        foreach ($paramsRules as $field => $rules) {
            list($ruleVariable, $type) = array_pad(explode(":", $field), 2, 'string');
            $validatorClass = "\SimplePHP\Validators\\" . ucfirst($type) . "Validator";
            if (!class_exists($validatorClass)) {
                throw new \SimplePHP\Exception\UndefinedValidateType("O tipo de validação '" . "\SimplePHP\Validators\\" . ucfirst($type) . "Validator' não foi definido!");
            }
            if (is_array($rules['rules']) && !empty($rules['rules'])) {
                if (isset($rules['rules']['invalid']) && !empty($rules['rules']['invalid'])) {
                    $invalidMessage = $rules['rules']['invalid'];
                    unset($rules['rules']['invalid']);
                } else if (isset($rules['invalid']) && !empty($rules['invalid'])) {
                    $invalidMessage = $rules['invalid'];
                } else {
                    $invalidMessage = '';
                }
                $validator = new $validatorClass($variables, $ruleVariable, $rules['rules'], $invalidMessage);
                $this->errors = array_merge($this->errors, $validator->getErrors());
            }
        }
        if ($this->errors) {
            foreach ($this->errors as $error) {
                \SimplePHP\Exception\Error::warningMessage($error);
            }
        }
    }

    public function renderErrors($response){
        return \SimplePHP\Exception\ErrorRegister::render($response);
    }
    public function getError() {
        return $this->errors;
    }

    public function viewRender($response, $template, array $args = []) {
       return $GLOBALS['App']->viewRender($response, $template, $args);
    }

}
