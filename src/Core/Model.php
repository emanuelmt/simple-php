<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Core;

abstract class Model {

    protected $conn;
    protected $variables;
    protected $errors = [];
    protected $tableName;

    public function __construct($conn = null) {
        
    }

    protected function validate(array &$variables, array $paramsRules, bool $breakOnInvalid = true) {
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
            if ($breakOnInvalid) {
                \SimplePHP\Exception\ErrorRegister::render($breakOnInvalid);
            }
        }
    }

    public function getError() {
        return $this->errors;
    }

}
