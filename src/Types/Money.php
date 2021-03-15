<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Money extends \SimplePHP\Core\SimpleObject {

    private $number;
    private $negative = false;
    private $intPart;
    private $decimalPart;
    private $decimalSeparator;
    private $thousandsSeparator;
    private $currencyPrefix;
    protected $valid = false;

    public function __construct($number = 0, $decimalSeparator = null) {
        $this->parse($number, $decimalSeparator);
    }

    public function parse($number, $decimalSeparator = null) {
        $this->error = null;
        $this->number = trim($number);
        $this->decimalSeparator = $decimalSeparator;
//        if (preg_match('/^(\-)?(\s)?([R]\$|\$)?(\s?)(\d{1,3}(\,\d{3})*|(\d+))(\.\d+)?$/i', $this->number) && $this->decimalSeparator == '.') {
        if (preg_match('/^(\-)?(\s)?([R]\$|\$)?(\s?)(\d{1,3}(\,\d{3})*|(\d+))(\.\d+)?$/i', $this->number) && ($decimalSeparator ? $decimalSeparator == '.' : true)) {
            $this->decimalSeparator = '.';
            $this->thousandsSeparator = ',';
            $this->valid = true;
//        } else if (preg_match('/^(\-)?(\s)?([R]\$|\$)?(\s?)(\d{1,3}(\.\d{3})*|(\d+))(\,\d+)?$/i', $this->number) && $this->decimalSeparator == ',') {
        } else if (preg_match('/^(\-)?(\s)?([R]\$|\$)?(\s?)(\d{1,3}(\.\d{3})*|(\d+))(\,\d+)?$/i', $this->number) && ($decimalSeparator ? $decimalSeparator == ',' : true)) {
            $this->decimalSeparator = ',';
            $this->thousandsSeparator = '.';
            $this->valid = true;
        } else {
            $this->setError('invalid_number_format', "O valor informado não possui um formato válido!");
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

    public function format($decimals = 2, $decimalSeparator = '.', $thousandsSeparator = '', $currencyPrefix = 'R$ ', $round = 0) {
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
        return ($this->negative ? '-' : '') . $currencyPrefix . number_format(floatval($number), (int) $decimals, $decimalSeparator, $thousandsSeparator);
    }

    public function __toString() {
        if ((int) $this->decimalPart) {
            return $this->format(strlen($this->decimalPart), '.', '', '');
        } else {
            return $this->format(2, '.', '', '');
        }
    }

    public function toFloat($decimals = 2, $round = 0) {
        return ($this->format($decimals, '.', '', '', $round));
    }

    public function toInt($round = 0) {
        return intval($this->format(false, '.', '', '', $round));
    }
}
