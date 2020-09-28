<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Date extends \SimplePHP\Core\SimpleObject {

    private $dateString;
    private $year;
    private $month;
    private $day;
    private $hour;
    private $minute;
    private $second;
    private $withTime;

    public function __construct($dateString = '', $withTime = false) {
        $this->dateString = $dateString;
        $this->withTime = $withTime;
        $this->parse($dateString);
    }

    public function parse($dateString) {
        $this->error = null;
        $this->dateString = $dateString;
        $regexSeparator = "([^0-9]{1})";
        $regexYear = "\d{4}";
        $regexMonth = "(([0-9]|(0)[0-9])|((1)[0-2]))";
        $regexDay = "([0]?[0-9]|[1-2][0-9]|(3)[0-1])";
        $regexTime = "(([0-9]|[0-1][0-9]|(2)[0-3])({$regexSeparator}([0-9]|[0-5][0-9])({$regexSeparator}([0-9]|[0-5][0-9]))?)?)?";
        $regexTimeOnly = "(([0-9]|[0-1][0-9]|(2)[0-3])((:)([0-9]|[0-5][0-9])((:)([0-9]|[0-5][0-9]))))";
        $explode = array_pad(preg_split("/([^0-9]{1})/i", $dateString, 6), 6, 0);
        if (preg_match("/^{$regexDay}{$regexSeparator}{$regexMonth}{$regexSeparator}{$regexYear}{$regexSeparator}?{$regexTime}$/i", $dateString)) {
            list($this->day, $this->month, $this->year, $this->hour, $this->minute, $this->second) = $explode;
        } else if (preg_match("/^{$regexYear}{$regexSeparator}{$regexMonth}{$regexSeparator}{$regexDay}{$regexSeparator}?{$regexTime}$/i", $dateString)) {
            list($this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second) = $explode;
        } else if (preg_match("/^{$regexTimeOnly}$/i", $dateString)) {
            list($this->hour, $this->minute, $this->second) = $explode;
        } else if ($dateString) {
            $this->setError('invalid_date_format', "A data informada não está em um formato válido!");
        }
        $this->check();
        return $this;
    }

    public function check($dateString = '') {
        if ($dateString) {
            $this->parse($dateString);
        }
        if (!$this->getError()) {
            if ($this->month !== null && $this->day !== null && $this->year !== null) {
                if (!checkdate($this->month, $this->day, $this->year)) {
                    $this->valid = false;
                    $this->setError('invalid_date', "A data informada não é válida!");
                    return false;
                } else {
                    $this->valid = true;
                    return true;
                }
            } else if ($this->hour !== null || $this->minute !== null || $this->second !== null) {
                $this->valid = true;
                return true;
            } else {
                $this->valid = false;
                $this->setError('invalid_date', "A data informada não é válida!");
                return false;
            }
        } else {
            $this->setError('invalid_date', "A data informada não é válida!");
            $this->valid = false;
            return false;
        }
    }

    public function format($pattern = null) {
        if (!$pattern) {
            if ($this->hasDate() && $this->withTime) {
                $pattern = "d/m/y h:i:s";
            } else if ($this->hasDate()) {
                $pattern = "d/m/y";
            } else if ($this->hasDate()) {
                $pattern = "h:i:s";
            } else {
                $pattern = "";
            }
        }
        if (!$this->getError()) {
            return str_replace(['y', 'm', 'd', 'h', 'i', 's'], [$this->year, str_pad($this->month, 2, '0', STR_PAD_LEFT), str_pad($this->day, 2, '0', STR_PAD_LEFT), str_pad($this->hour, 2, '0', STR_PAD_LEFT), str_pad($this->minute, 2, '0', STR_PAD_LEFT), str_pad($this->second, 2, '0', STR_PAD_LEFT)], mb_strtolower($pattern));
        } else {
            return '';
        }
    }

    private function hasDate() {
        return($this->month !== null && $this->day !== null && $this->year !== null);
    }

    private function hasTime() {
        return($this->hour != null || $this->minute != null || $this->second != null);
    }

    public function __toString() {
        if ($this->hasDate()) {
            if ($this->withTime && $this->hasTime()) {
                return $this->format('y-m-d h:i:s');
            } else {
                return $this->format('y-m-d');
            }
        } else if ($this->hasTime()) {
            return $this->format('h:i:s');
        } else {
            return '';
        }
    }

    public function lessThen($dateString) {
        $date = new Date($dateString);
        return (new \DateTime($this->format('y-m-d h:i:s')) < new \DateTime($date->format('y-m-d h:i:s')));
    }

    public function biggerThen($dateString) {
        $date = new Date($dateString);
        return (new \DateTime($this->format('y-m-d h:i:s')) > new \DateTime($date->format('y-m-d h:i:s')));
    }

    public function lessEqualsThen($dateString) {
        $date = new Date($dateString);
        return (new \DateTime($this->format('y-m-d h:i:s')) <= new \DateTime($date->format('y-m-d h:i:s')));
    }

    public function biggerEqualsThen($dateString) {
        $date = new Date($dateString);
        return (new \DateTime($this->format('y-m-d h:i:s')) >= new \DateTime($date->format('y-m-d h:i:s')));
    }

    public function equalsThen($dateString) {
        $date = new Date($dateString);
        return (new \DateTime($this->format('y-m-d h:i:s')) == new \DateTime($date->format('y-m-d h:i:s')));
    }

}
