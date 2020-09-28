<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Exception;

/**
 * Description of Error
 *
 * @author emanu
 */
class Error {

    private $message;
    private $title;
    private $type;
    private $code;
    private $previus;

    public function __construct($message, $title = '', $type = 'INFO', $code = null, \Throwable $previus = null) {
        $this->message = $message;
        $this->title = $title;
        $this->type = $this->errorClassify($type);
        $this->code = $code;
        $this->previus = $previus;
    }

    private function errorClassify($type) {
        if ($type == "SYSTEM") {
            return "SYSTEM";
        } else if ($type == "REQUEST") {
            return "REQUEST";
        } else if ($type == E_USER_ERROR || $type == "ERROR") {
            return "ERROR";
        } else if ($type == E_USER_WARNING || $type == "WARNING") {
            return "WARNING";
        } else if ($type == "SUCCESS") {
            return "SUCCESS";
        } else {
            return "INFO";
        }
    }

    public function getMessage() {
        return $this->message;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getType() {
        return $this->type;
    }

    public function getCode() {
        return $this->code;
    }

    public function getTrace() {
        return $this->previus;
    }

    public static function errorMessage($message, $title = 'Ooops!', $code = null, $breakScript = true, $previus = null, $response = null) {
        return ErrorRegister::register(new Error($message, $title, "ERROR", $code, $previus), $breakScript, $response);
    }

    public static function sucessMessage($message, $title = 'Iuuupi!', $code = null, $breakScript = false, $previus = null, $response = null) {
        return ErrorRegister::register(new Error($message, $title, "SUCCESS", $code, $previus), $breakScript, $response);
    }

    public static function warningMessage($message, $title = 'Ahhh...', $code = null, $breakScript = false, $previus = null, $response = null) {
        return ErrorRegister::register(new Error($message, $title, "WARNING", $code, $previus), $breakScript, $response);
    }

    public static function infoMessage($message, $title = '', $code = null, $breakScript = false, $previus = null, $response = null) {
        return ErrorRegister::register(new Error($message, $title, "INFO", $code, $previus), $breakScript, $response);
    }

}
