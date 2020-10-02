<?php

/*
 * Código desenvolvido por Emanuel Marques.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados.
 */

namespace SimplePHP\Security;

/**
 * Description of Crypt
 *
 * @author emanu
 */
class Crypt {

    private $Pass;
    private $Salt;
    private $Cost;
    private $Result;

    public function __construct($string, $cost = "rounds=6782") {
        $this->Pass = $string;
        $this->Cost = $cost;
        $this->Salt = $this->generateSalt();
        $this->Cryptography();
    }

    public function getResult() {
        return $this->Result;
    }

    public function compare($hash) {
        if (crypt($this->Pass, "$6$" . $this->Cost . '$' . $hash) === "$6$" . $this->Cost . '$' . $hash) {
            return true;
        } else {
            return false;
        }
    }

    private function Cryptography() {
        if ($this->Pass) {
            $this->Result = substr(crypt($this->Pass, '$6$' . $this->Cost . '$' . $this->Salt . '$'), 15);
        }
    }

    private static function generateSalt() {
        // Salt seed
        $seed = uniqid(mt_rand(), true);
        // Generate salt
        $salt = str_replace('+', '.', base64_encode($seed));
        return substr($salt, 0, 25);
    }

}
