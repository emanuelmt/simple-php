<?php

namespace SimplePHP\Validators;

class StringValidator extends GenericValidator {

    protected function ruleMax(&$variable, $params) {
        return (is_array($params) ? strlen($this->variables[$variable]) <= $params[0] : strlen($this->variables[$variable]) <= $params);
    }

    protected function ruleMin(&$variable, $params) {
        return (is_array($params) ? strlen($this->variables[$variable]) >= $params[0] : strlen($this->variables[$variable]) >= $params);
    }

}
