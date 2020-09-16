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

    private $type;
    private $message;
    private $previus;

    public function __construct($message, $type = 'INFO', \Throwable $previus = null) {
        $this->message = $message;
        $this->type = $this->errorClassify($type);
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

    public function getType() {
        return $this->type;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getTrace() {
        return $this->previus;
    }
    
    public static function errorMessage($message, $breakScript = true, $previus = null){
        return ErrorRegister::register(new Error($message, "ERROR", $previus), $breakScript);
    }
    
    public static function sucessMessage($message, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "SUCCESS", $previus), $breakScript);
    }
    
    public static function warningMessage($message, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "WARNING", $previus), $breakScript);
    }
    
    public static function infoMessage($message, $breakScript = false, $previus = null){
        return ErrorRegister::register(new Error($message, "INFO", $previus), $breakScript);
    }

}
