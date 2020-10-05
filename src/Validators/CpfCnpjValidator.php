<?php

namespace SimplePHP\Validators;

class CpfCnpjValidator extends GenericValidator {

    protected $type = "\SimplePHP\Types\CpfCnpj";

    protected function ruleIsCpf(&$variable) {
        return ($this->variable->typeOf() == "CPF");
    }

    protected function ruleIsCnpj(&$variable) {
        return ($this->variable->typeOf() == "CNPJ");
    }

}
