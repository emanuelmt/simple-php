<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Phone extends \SimplePHP\Core\SimpleObject {

     private $phoneString;
     private $dddRequired;
     private $ddiRequired;
     private $ddd;
     private $ddi;
     private $firstPart;
     private $secondPart;

     public function __construct($phoneString = '', $dddRequired = true, $ddiRequired = false) {
          $this->dddRequired = $dddRequired;
          $this->parse($phoneString);
     }

     public function parse($phoneString) {
          $this->error = null;
          $this->phoneString = $phoneString;
          $regex = '/^(?:(?:\+|00)?(55)\s?)?(?:\(?([1-9][0-9])\)?\s?)?(?:(?:((?:9\d{4}|[2-9]\d{3}))\-?(\d{4}))|(?:((?:9\d{3}))\-?(\d{5})))$/';
          if (preg_match($regex, $this->phoneString, $explode) != false) {
               $this->ddi = $explode[1];
               $this->ddd = $explode[2];
               $this->firstPart = ($explode[3] ? $explode[3] : $explode[5]);
               $this->secondPart = ($explode[4] ? $explode[4] : $explode[6]);
          } else if ($phoneString) {
               $this->setError('invalid_phone_format', "O telefone informado não está em um formato válido!");
          }
          $this->check();
          return $this;
     }

     public function check($phoneString = '') {
          if ($phoneString) {
               $this->parse($phoneString);
          }
          if (!$this->getError()) {
               if ($this->ddiRequired && !$this->ddi) {
                    $this->valid = false;
                    $this->setError('invalid_phone', "O DDI não foi informado!");
                    return false;
               } else if ($this->dddRequired && !$this->ddd) {
                    $this->valid = false;
                    $this->setError('invalid_phone', "O DDD não foi informado!");
                    return false;
               } else if ($this->ddd && ($this->ddd < 11 || $this->ddd > 99 || in_array($this->ddd, [20, 23, 25, 26, 29, 30, 36, 39, 40, 50, 52, 56, 57, 58, 59, 60, 70, 72, 76, 78, 80, 90]))) {
                    $this->valid = false;
                    $this->setError('invalid_phone', "O DDD informado não é válido!");
                    return false;
               } else if (!$this->firstPart || !$this->secondPart) {
                    $this->valid = false;
                    $this->setError('invalid_phone', "O telefone informado não é válido!");
                    return false;
               } else {
                    $this->valid = true;
                    return true;
               }
          } else {
               $this->valid = false;
               return false;
          }
     }

     public function format($pattern = null) {
          if ($pattern) {
               $retorno = str_replace(['ddi', 'ddd', 'fp', 'sp'], [($this->ddi ? $this->ddi : "55"), ($this->ddd ? $this->ddd : "00"), ($this->firstPart ? $this->firstPart : "0000"), ($this->secondPart ? $this->secondPart : "0000")], $pattern);
          } else {
               $retorno = (
                      ($this->ddi ? "+" . $this->ddi . " " : "") .
                      ($this->ddd ? "(" . $this->ddd . ") " : "") .
                      ($this->firstPart ? $this->firstPart . "-" : "") .
                      ($this->secondPart ? $this->secondPart : "")
                      );
          }
          return $retorno;
     }

     public function phoneOnly($separator = "") {
          return (
                 ($this->firstPart ? $this->firstPart . $separator : "") .
                 ($this->secondPart ? $this->secondPart : "")
                 );
     }

     public function hasDDI() {
          return $this->ddi;
     }

     public function hasDDD() {
          return $this->ddd;
     }

     public function __toString() {
          return $this->ddi . $this->ddd . $this->firstPart . $this->secondPart;
     }

}
