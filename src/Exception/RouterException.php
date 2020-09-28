<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Exception;

class RouterException extends SimpleException {

    protected $userMessage = "Os argumentos do método \SimplePHP\Application::startRouter precisam ser implementações de \SimplePHP\SimpleRoute ou uma função.";

}
