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
    private $type;
    private $code;
    private $previus;

    public function __construct($message, $type = 'INFO', $code = null, \Throwable $previus = null) {
        $this->message = $message;
        $this->type = $this->errorClassify($type);
        $this->code = $code;
        $this->previus = $previus;
    }

    private function errorClassify($type) {
        if ($type == "SYSTEM") {
            return "SYSTEM";
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

    public function getType() {
        return $this->type;
    }
    
    public function getCode() {
        return $this->code;
    }

    public function getTrace() {
        return $this->previus;
    }
    
    public static function errorMessage($message, $code = null, $breakScript = true, $previus = null){
        return ErrorRegister::register(new Error($message, "ERROR", $code, $previus), $breakScript);
    }
    
    public static function sucessMessage($message, $code = null, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "SUCCESS", $code, $previus), $breakScript);
    }
    
    public static function warningMessage($message, $code = null, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "WARNING", $code, $previus), $breakScript);
    }
    
    public static function infoMessage($message, $code = null, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "INFO", $code, $previus), $breakScript);
    }

}
