<?php

namespace SimplePHP\Validators;

class GenericValidator {

    protected $variables;
    protected $variable;
    protected $type;
    protected $errors = [];

    public function __construct($variables, $ruleVariable, $rules, $invalidMessage = null) {
        $this->variables = $variables;

        if ($this->type && isset($this->variables[$ruleVariable])){
            $this->variable = new $this->type($variables[$ruleVariable]);
            if (!$this->variable->isValid()) {
                $this->errors[] = ($invalidMessage ? $invalidMessage : $this->variable->getError()['description']);
                return;
            }
        }
        
        if (isset($rules['required']) && !$this->ruleRequired($ruleVariable)) {
            $this->errors[$ruleVariable][] = $rules['required'];
        } else if ($this->ruleRequired($ruleVariable)) {
            if ($this->variable == null) {
                $this->variable = $this->variables[$ruleVariable];
            }
            foreach ($rules as $rule => $message) {
                $ruleSeparators = "/( && )|( \|\| )|(&{2})|(\|{2})(&)|(\|)/";
                $validationRule = (preg_match($ruleSeparators, $rule) ? preg_split($ruleSeparators, $rule, -1, PREG_SPLIT_NO_EMPTY) : [$rule]);

                foreach ($validationRule as $validation) {
                    $ruleMethod = 'rule' . ucfirst(explode('(', $validation)[0]);
                    $ruleParams = preg_split('/(\()|(,)|(\))/', $validation, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($ruleParams) > 0) {
                        unset($ruleParams[0]);
                        $ruleParams = array_values($ruleParams);
                    }
                    if (!method_exists($this, $ruleMethod)) {
                        throw new \SimplePHP\Exception\UndefinedValidateRule("A regra de validação '{$ruleMethod}' não foi definida!");
                    }
                    if (!$this->{$ruleMethod}($ruleVariable, $ruleParams)) {
                        $this->errors[$ruleVariable][] = $message;
                    }
                }
            }
        }
    }

    private function getInvalidMessage(){
        
    }
    
    public function getErrors() {
        return $this->errors;
    }

    protected function ruleRequired(&$variable) {
        return isset($this->variables[$variable]);
    }

    protected function ruleNotEmpty(&$variable) {
        return isset($this->variables[$variable]) && !empty($this->variables[$variable]);
    }

    protected function ruleNotEqual(&$variable, $params) {
        return (is_array($params) ? !in_array($this->variables[$variable], $params) : ($this->variables[$variable] != $params));
    }

    protected function ruleEqual(&$variable, $params) {
        return (is_array($params) ? !in_array($this->variables[$variable], $params) : ($this->variables[$variable] != $params));
    }

    protected function ruleMax(&$variable, $params) {
        return (is_array($params) ? $this->variables[$variable] <= $params[0] : $this->variables[$variable] <= $params);
    }

    protected function ruleMin(&$variable, $params) {
        return (is_array($params) ? $this->variables[$variable] >= $params[0] : $this->variables[$variable] >= $params);
    }

    protected function ruleLessThen(&$variable, $params) {
        return (is_array($params) ? $this->variables[$variable] < $params[0] : $this->variables[$variable] < $params);
    }

    protected function ruleBiggerThen(&$variable, $params) {
        return (is_array($params) ? $this->variables[$variable] > $params[0] : $this->variables[$variable] > $params);
    }

}
