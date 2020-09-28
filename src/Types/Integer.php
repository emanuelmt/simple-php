<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Integer extends \SimplePHP\Core\SimpleObject {

    private $number;
    private $negative = false;
    private $intPart;
    private $thousandsSeparator;
    protected $valid = false;

    public function __construct($number = 0) {
        $this->parse($number);
    }

    public function parse($number) {
        $this->error = null;
        $this->number = trim($number);

        if (preg_match('/^\-?(\d{1,3}(\,\d{3})*|(\d+))$/i', $this->number)) {
            $this->decimalSeparator = '.';
            $this->thousandsSeparator = ',';
            $this->valid = true;
        } else if (preg_match('/^\-?(\d{1,3}(\.\d{3})*|(\d+))$/i', $this->number)) {
            $this->decimalSeparator = ',';
            $this->thousandsSeparator = '.';
            $this->valid = true;
        } else {
            $this->setError('invalid_number_format', "O número informado não possui um formato válido para um inteiro!");
            $this->valid = false;
        }
        $this->negative = (isset($this->number[0]) && $this->number[0] == '-' ? true : false);
        $this->intPart = intval(preg_replace("/[^0-9]/", "", $this->number));
        return $this;
    }

    public function format() {
        return ($this->negative ? '-' : '') . $this->intPart;
    }

    public function __toString() {
        return $this->format();
    }

}
