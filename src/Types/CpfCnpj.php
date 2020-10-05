<?php

namespace SimplePHP\Types;

/**
 * ValidaCPFCNPJ valida e formata CPF e CNPJ
 *
 * Exemplo de uso:
 * $cpf_cnpj  = new ValidaCPFCNPJ('71569042000196');
 * $formatted = $cpf_cnpj->format(); // 71.569.042/0001-96
 * $valida    = $cpf_cnpj->check(); // True -> Válido
 *
 * @package  valida-cpf-cnpj
 * @author   Luiz Otávio Miranda <contato@todoespacoonline.com/w>
 * @version  v1.4
 * @access   public
 * @see      http://www.todoespacoonline.com/w/
 */
class CpfCnpj extends \SimplePHP\Core\SimpleObject {

    /**
     * Configura o valor (Construtor)
     * 
     * Remove caracteres inválidos do CPF ou CNPJ
     * 
     * @param string $valor - O CPF ou CNPJ
     */
    private $cpfCnpjString;

    function __construct($valor = null) {
        // Deixa apenas números no valor
        $this->cpfCnpjString = (string) preg_replace('/[^0-9]/', '', $valor);
        $this->check();
    }

    /**
     * Verifica se é CPF ou CNPJ
     * 
     * Se for CPF tem 11 caracteres, CNPJ tem 14
     * 
     * @access protected
     * @return string CPF, CNPJ ou false
     */
    public function typeOf() {
        // Verifica CPF
        if (strlen($this->cpfCnpjString) === 11) {
            return 'CPF';
        }
        // Verifica CNPJ
        elseif (strlen($this->cpfCnpjString) === 14) {
            return 'CNPJ';
        }
        // Não retorna nada
        else {
            return false;
        }
    }

    /**
     * Verifica se todos os números são iguais
     * 	 * 
     * @access protected
     * @return bool true para todos iguais, false para números que podem ser válidos
     */
    protected function hasEqualsDigits() {
        // Todos os caracteres em um array
        $caracteres = str_split($this->cpfCnpjString);

        // Considera que todos os números são iguais
        $todos_iguais = true;

        // Primeiro caractere
        $last_val = $caracteres[0];

        // Verifica todos os caracteres para detectar diferença
        foreach ($caracteres as $val) {

            // Se o último valor for diferente do anterior, já temos
            // um número diferente no CPF ou CNPJ
            if ($last_val != $val) {
                $todos_iguais = false;
            }

            // Grava o último número checado
            $last_val = $val;
        }

        // Retorna true para todos os números iguais
        // ou falso para todos os números diferentes
        return $todos_iguais;
    }

    /**
     * Multiplica dígitos vezes posições
     *
     * @access protected
     * @param  string    $digits      Os digitos desejados
     * @param  int       $positions     A posição que vai iniciar a regressão
     * @param  int       $sumDigits A soma das multiplicações entre posições e dígitos
     * @return int                     Os dígitos enviados concatenados com o último dígito
     */
    protected function calculateDigitsPositions($digits, $positions = 10, $sumDigits = 0) {
        // Faz a soma dos dígitos com a posição
        // Ex. para 10 posições:
        //   0    2    5    4    6    2    8    8   4
        // x10   x9   x8   x7   x6   x5   x4   x3  x2
        //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
        for ($i = 0; $i < strlen($digits); $i++) {
            // Preenche a soma com o dígito vezes a posição
            $sumDigits = $sumDigits + ( $digits[$i] * $positions );

            // Subtrai 1 da posição
            $positions--;

            // Parte específica para CNPJ
            // Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
            if ($positions < 2) {
                // Retorno a posição para 9
                $positions = 9;
            }
        }

        // Captura o resto da divisão entre $sumDigits dividido por 11
        // Ex.: 196 % 11 = 9
        $sumDigits = $sumDigits % 11;

        // Verifica se $sumDigits é menor que 2
        if ($sumDigits < 2) {
            // $sumDigits agora será zero
            $sumDigits = 0;
        } else {
            // Se for maior que 2, o resultado é 11 menos $sumDigits
            // Ex.: 11 - 9 = 2
            // Nosso dígito procurado é 2
            $sumDigits = 11 - $sumDigits;
        }

        // Concatena mais um dígito aos primeiro nove dígitos
        // Ex.: 025462884 + 2 = 0254628842
        $cpf = $digits . $sumDigits;

        // Retorna
        return $cpf;
    }

    /**
     * Valida CPF
     *
     * @author                Luiz Otávio Miranda <contato@todoespacoonline.com/w>
     * @access protected
     * @param  string    $cpf O CPF com ou sem pontos e traço
     * @return bool           True para CPF correto - False para CPF incorreto
     */
    protected function checkCpf() {
        // Captura os 9 primeiros dígitos do CPF
        // Ex.: 02546288423 = 025462884
        $digits = substr($this->cpfCnpjString, 0, 9);

        // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
        $newCpf = $this->calculateDigitsPositions($digits);

        // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
        $newCpf = $this->calculateDigitsPositions($newCpf, 11);

        // Verifica se todos os números são iguais
        if ($this->hasEqualsDigits()) {
            return false;
        }

        // Verifica se o novo CPF gerado é idêntico ao CPF enviado
        if ($newCpf === $this->cpfCnpjString) {
            // CPF válido
            return true;
        } else {
            // CPF inválido
            return false;
        }
    }

    /**
     * Valida CNPJ
     *
     * @author                  Luiz Otávio Miranda <contato@todoespacoonline.com/w>
     * @access protected
     * @param  string     $cnpj
     * @return bool             true para CNPJ correto
     */
    protected function checkCnpj() {
        // O valor original
        $originalCnpj = $this->cpfCnpjString;

        // Captura os primeiros 12 números do CNPJ
        $firstCnpjDigits = substr($this->cpfCnpjString, 0, 12);

        // Faz o primeiro cálculo
        $firstCalculation = $this->calculateDigitsPositions($firstCnpjDigits, 5);

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $secondCalculation = $this->calculateDigitsPositions($firstCalculation, 6);

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $secondCalculation;

        // Verifica se todos os números são iguais
        if ($this->hasEqualsDigits()) {
            return false;
        }

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $originalCnpj) {
            return true;
        }
    }

    /**
     * Valida
     * 
     * Valida o CPF ou CNPJ
     * 
     * @access public
     * @return bool      True para válido, false para inválido
     */
    public function check() {
        if ($this->typeOf() === 'CPF') {
            if ($this->checkCpf()) {
                $this->valid = true;
                return true;
            } else {
                $this->setError('invalid_cpf', "O CPF informado não é válido!");
            }
        } elseif ($this->typeOf() === 'CNPJ') {
            if ($this->checkCnpj()) {
                $this->valid = true;
                return true;
            } else {
                $this->setError('invalid_cnpj', "O CNPJ informado não é válido!");
            }
        } else {
            $this->setError('invalid_cpfCnpj_format', "O formato do CPF/CNPJ informado não é válido!");
        }
        $this->valid = false;
        return false;
    }

    /**
     * Formata CPF ou CNPJ
     *
     * @access public
     * @return string  CPF ou CNPJ formatado
     */
    public function format() {
        // O valor formatado
        $formatted = false;

        // Valida CPF
        if ($this->typeOf() === 'CPF') {
            // Verifica se o CPF é válido
            if ($this->valid) {
                // Formata o CPF ###.###.###-##
                $formatted = substr($this->cpfCnpjString, 0, 3) . '.';
                $formatted .= substr($this->cpfCnpjString, 3, 3) . '.';
                $formatted .= substr($this->cpfCnpjString, 6, 3) . '-';
                $formatted .= substr($this->cpfCnpjString, 9, 2) . '';
            }
        }
        // Valida CNPJ
        elseif ($this->typeOf() === 'CNPJ') {
            // Verifica se o CPF é válido
            if ($this->$this->valid) {
                // Formata o CNPJ ##.###.###/####-##
                $formatted = substr($this->cpfCnpjString, 0, 2) . '.';
                $formatted .= substr($this->cpfCnpjString, 2, 3) . '.';
                $formatted .= substr($this->cpfCnpjString, 5, 3) . '/';
                $formatted .= substr($this->cpfCnpjString, 8, 4) . '-';
                $formatted .= substr($this->cpfCnpjString, 12, 14) . '';
            }
        }

        // Retorna o valor 
        return $formatted;
    }

    public function __toString() {
        return ($this->valid ? $this->cpfCnpjString : "");
    }

}
