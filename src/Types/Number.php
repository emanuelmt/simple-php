<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Number extends \SimplePHP\Core\SimpleObject {

    private $number;
    private $negative = false;
    private $intPart;
    private $decimalPart;
    private $decimalSeparator;
    private $thousandsSeparator;
    protected $valid = false;

    public function __construct($number = 0, $decimalSeparator = '.') {
        $this->parse($number, $decimalSeparator);
    }

    public function parse($number, $decimalSeparator = '.') {
        $this->error = null;
        $this->number = trim($number);
        $this->decimalSeparator = $decimalSeparator;

        if (preg_match('/^\-?(\d{1,3}(\,\d{3})*|(\d+))(\.\d+)?$/i', $this->number) && $this->decimalSeparator == '.') {
            $this->decimalSeparator = '.';
            $this->thousandsSeparator = ',';
            $this->valid = true;
        } else if (preg_match('/^\-?(\d{1,3}(\.\d{3})*|(\d+))(\,\d+)?$/i', $this->number) && $this->decimalSeparator == ',') {
            $this->decimalSeparator = ',';
            $this->thousandsSeparator = '.';
            $this->valid = true;
        } else {
            $this->setError('invalid_number_format', "O número informado não possui um formato válido!");
            $this->valid = false;
        }
        $this->negative = (isset($this->number[0]) && $this->number[0] == '-' ? true : false);
        $dotPos = strrpos($this->number, '.');
        $commaPos = strrpos($this->number, ',');
        $separator = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$separator) {
            $floatVal = intval(preg_replace("/[^0-9]/", "", $this->number));
        } else {
            $floatVal = floatval(
                preg_replace("/[^0-9]/", "", substr($this->number, 0, $separator)) . '.' .
                preg_replace("/[^0-9]/", "", substr($this->number, $separator + 1, strlen($this->number)))
            );
        }
        if ($this->decimalSeparator == '.') {
            $numberParts = explode(".", $floatVal);
        } else {
            $numberParts = explode(",", $floatVal);
        }
        $this->intPart = $numberParts[0] ?? null;
        $this->decimalPart = $numberParts[1] ?? null;

        return $this;
    }

    public function format($decimals = false, $decimalSeparator = '.', $thousandsSeparator = '', $round = 0) {
        if ($decimals === false) {
            $decimals = strlen($this->decimalPart);
        }
        if ((int) $this->decimalPart && (int) $decimals) {
            if (strlen($this->decimalPart) > (int) $decimals && $round) {
                $number = round($this->intPart . '.' . $this->decimalPart, $decimals);
            } else if (strlen($this->decimalPart) > (int) $decimals && !$round) {
                $number = $this->intPart . '.' . substr($this->decimalPart, 0, (int) $decimals);
            } else {
                $number = $this->intPart . '.' . str_pad($this->decimalPart, $decimals, "0");
            }
        } else {
            $number = ($this->intPart . ($this->decimalPart ? '.' . $this->decimalPart : ''));
        }
        return ($this->negative ? '-' : '') . number_format($number, (int) $decimals, $decimalSeparator, $thousandsSeparator);
    }

    public function __toString() {
        if ((int) $this->decimalPart) {
            return $this->format(strlen($this->decimalPart));
        } else {
            return $this->format();
        }
    }
    
    public static function numbersOnly($string){
        return preg_replace("/\D/", "", $string);
    }

}
