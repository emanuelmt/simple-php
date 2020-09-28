<?php

/*
 * Código de propriedade da empresa SimplesTI.
 * É restrita qualquer alteração, cópia ou
 * utilização não autorizada do mesmo.
 * Todos os direitos reservados. SimplesTI - 2018
 */

namespace SimplePHP\Exception;

class UndefinedValidateRule extends SimpleException {

    protected $userMessage = "Foi informada uma regra de validação indefinida.";

}
