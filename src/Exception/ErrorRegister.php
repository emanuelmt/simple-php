<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Exception;

class ErrorRegister {

    public static function initialize() {
        $GLOBALS['Errors'] = [];
    }

    public static function register(Error $error, $breakScript = false) {
        $GLOBALS['Errors'][] = $error;
        if ($breakScript) {
            self::render();
            exit();
        }
    }

    public static function getErrors() {
        return $GLOBALS['Errors'];
    }

    public static function render() {
        if (self::getErrors()) {
            var_dump(self::getErrors());
            self::initialize();
        }
    }

}
