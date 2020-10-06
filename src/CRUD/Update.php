<?php

/**
 * <b>Update.class:</b>
 * Classe responsável por atualizações genéricas no banco de dados!
 *
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace SimplePHP\CRUD;

class Update extends Conn {

    private $Tabela;
    private $Dados;
    private $Termos;
    private $Places;
    private $Result;
    private $Error;

    /** @var PDOStatement */
    private $Update;

    /** @var PDO */
    protected $Conn;

    public function ExeUpdate($Tabela, array $Dados, $Termos, $ParseString) {
        $this->Tabela = (string) $Tabela;
        foreach ($Dados as &$value) {
            if ($value == '')
                $value = null;
        }
        $this->Dados = $Dados;
        $this->Termos = $Termos;

        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getError() {
        return $this->Error;
    }

    public function getRowCount() {
        return $this->Update->rowCount();
    }

    public function setPlaces($ParseString) {
        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    /**
     * **************************************************
     * **************** PRIVATE METHODS *****************
     * **************************************************
     */
    //Obtém o PDO e prepara a Query
    private function Connect() {
        if (!$this->Conn) {
            $this->Conn = parent::getConn();
        }
        $this->Update = $this->Conn->prepare($this->Update);
        foreach ($this->Dados as $Key => $Value) {
            $this->Update->bindValue(':' . $Key, $Value, empty($Value) ? \PDO::PARAM_NULL : (is_int($Value) ? \PDO::PARAM_INT : (is_bool($Value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR)));
//            $field = explode("_", $Key);
//            if (strtoupper($field[0]) == "CD") {
//                $this->Update->bindValue(':' . $Key, (int) $Value, \PDO::PARAM_INT);
//            }else if($Key == "DS_SENHA"){
//                $this->Update->bindValue(':' . $Key, $Value, empty($Value) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
//            } else {
//                $Value = $Value == "NULL" ? null : $Value;
//                $this->Update->bindValue(':' . $Key, mb_strtoupper($Value), empty($Value) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
//            }
        }
        foreach ($this->Places as $Key => $Value) {
            $this->Update->bindValue(':' . $Key, mb_strtoupper($Value));
        }
    }

    //Cria a sintaxe da Query para Prepared Statements
    private function getSyntax() {
        foreach ($this->Dados as $Key => $Value) {
            $Places[] = $Key . ' = :' . $Key;
        }
        $Places = implode(', ', $Places);
        $this->Update = "UPDATE {$this->Tabela} SET {$Places} {$this->Termos}";
    }

    private function Execute() {
        $this->Connect();
        try {
            $this->Update->execute();
            $this->Result = true;
        } catch (\PDOException $e) {
            if ($this->Conn->inTransaction()) {
                $this->Rollback();
            }
            throw $e;
        }
    }

}
