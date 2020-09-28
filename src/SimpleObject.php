<?php

namespace SimplePHP;

class SimpleObject {

    protected $userMessage;
    protected $error;
    protected $valid;

    public function setError($code, $description) {
        $this->error = ["code" => $code, "description" => $description];
    }

    public function setMessage($description) {
        $this->userMessage = $description;
    }

    public function getError() {
        return $this->error;
    }

    public function getMessage() {
        return $this->userMessage;
    }

    public function toString() {
        return strval($this);
    }

    public function isValid() {
        return $this->valid;
    }

}
