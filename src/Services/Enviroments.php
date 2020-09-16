<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Services;

class Enviroments {

    public function __construct($configsFile) {
        $Configurations = parse_ini_file($configsFile);
        foreach ($Configurations as $key => $value) {
            define($key, $value);
        }
        define('ROOTPATH', dirname(__FILE__));
    }

}
