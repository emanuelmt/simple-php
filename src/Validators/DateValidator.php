<?php

namespace SimplePHP\Validators;

class DateValidator extends GenericValidator {
    
    protected $type = "\SimplePHP\Types\Date";

    protected function ruleNotEqual(&$variable, $params) {
        if (is_array($params)) {
            $param = new \SimplePHP\Types\Date($params[0]);
        } else {
            $param = new \SimplePHP\Types\Date($params);
        }
        if ($param->isValid()) {
            return !($param->equalsThen($this->variable));
        } else {
            throw new \SimplePHP\Exception\DateFormatException("O parâmetro de verificação de data não é válido!");
        }
    }

    protected function ruleEqual(&$variable, $params) {
        if (is_array($params)) {
            $param = new \SimplePHP\Types\Date($params[0]);
        } else {
            $param = new \SimplePHP\Types\Date($params);
        }
        if ($param->isValid()) {
            return ($param->equalsThen($this->variable));
        } else {
            throw new \SimplePHP\Exception\DateFormatException("O parâmetro de verificação de data não é válido!");
        }
    }

    protected function ruleMax(&$variable, $params) {
        if (is_array($params)) {
            $param = new \SimplePHP\Types\Date($params[0]);
        } else {
            $param = new \SimplePHP\Types\Date($params);
        }
        if ($param->isValid()) {
            return ($param->biggerEqualsThen($this->variable));
        } else {
            throw new \SimplePHP\Exception\DateFormatException("O parâmetro de verificação de data não é válido!");
        }
    }

    protected function ruleMin(&$variable, $params) {
        if (is_array($params)) {
            $param = new \SimplePHP\Types\Date($params[0]);
        } else {
            $param = new \SimplePHP\Types\Date($params);
        }
        if ($param->isValid()) {
            return ($param->lessEqualsThen($this->variable));
        } else {
            throw new \SimplePHP\Exception\DateFormatException("O parâmetro de verificação de data não é válido!");
        }
    }

}
