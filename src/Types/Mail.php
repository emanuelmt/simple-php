<?php

namespace SimplePHP\Types;

use \SimplePHP\Exception\Error;

class Mail extends \SimplePHP\Core\SimpleObject {

    private $mailString;
   

    public function __construct($mailString = '') {
        $this->mailString = $mailString;
        $this->parse($mailString);
    }

    public function parse($mailString) {
        $this->error = null;
        $this->valid = true;
        if (filter_var($mailString, FILTER_VALIDATE_EMAIL)) {
            $this->setError('invalid_mail', "O email informado não está em um formato válido!");
            $this->valid = false;
        }
        $this->check();
        return $this;
    }

    public function check($mailString = '') {
        if ($mailString) {
            $this->parse($mailString);
        }
        return $this->valid;
    }

    public function __toString() {
        return $this->mailString;
    }

}
