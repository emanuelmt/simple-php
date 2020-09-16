<?php

/**
 * <b>Read.class:</b>
 * Classe responsável por leituras genéricas no banco de dados!
 *
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace SimplePHP\CRUD;

class Read extends Conn {

    private $Select;
    private $Places;
    private $Result;

    /** @var PDOStatement */
    private $Read;

    /** @var PDO */
    protected $Conn;

    public function ExeRead($Tabela, $Termos = null, $ParseString = null) {
        if (!empty($ParseString)) {
            parse_str($ParseString, $this->Places);
        }
        $this->Select = "SELECT * FROM {$Tabela} {$Termos}";

        $this->Execute();
    }

    public function ExeReadFields($Fields, $Tabela, $Termos = null, $ParseString = null) {
        if (!empty($ParseString)) {
            parse_str($ParseString, $this->Places);
        }
        $this->Select = "SELECT {$Fields} FROM {$Tabela} {$Termos}";

        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getRowCount() {
        return $this->Read->rowCount();
    }

    public function FullRead($Query, $ParseString = null, $EqualsParams = false) {
        if (!$EqualsParams) {
            $this->Select = (string) $Query;
            if (!empty($ParseString)) {
                parse_str($ParseString, $this->Places);
            }
            $this->Execute();
        } else {
            if (!empty($ParseString)) {
                parse_str($ParseString, $this->Places);
                $places = [];
                foreach ($this->Places as $key => $value) {
                    $j = substr_count($Query, ":$key#");
                    for ($i = 0; $i < $j; $i++) {
                        $Query = preg_replace("/:$key#/", ":$key$i", $Query, 1);
                        $places["$key$i"] = $value;
                    }
                }
                $this->Places = $places;
            }
            $this->Select = (string) $Query;
            $this->Execute();
        }
    }

    public function setPlaces($ParseString) {
        parse_str($ParseString, $this->Places);
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
        $this->Read = $this->Conn->prepare($this->Select);
        $this->Read->setFetchMode(\PDO::FETCH_ASSOC);
    }

    //Cria a sintaxe da Query para Prepared Statements
    private function getSyntax() {
        if ($this->Places) {
            foreach ($this->Places as $Vinculo => $Valor) {
                if ($Vinculo == 'limit' || $Vinculo == 'offset' || $Vinculo == 'LIMIT' || $Vinculo == 'OFFSET') {
                    $Valor = (int) $Valor;
                }
                $this->Read->bindValue(":{$Vinculo}", $Valor, (is_int($Valor) ? \PDO::PARAM_INT : \PDO::PARAM_STR));
            }
        }
    }

    private function Execute() {
        $this->Connect();
        try {
            $this->getSyntax();
            $this->Read->execute();
            $this->Result = $this->Read->fetchAll();
        } catch (\PDOException $e) {
            if ($this->Conn->inTransaction()) {
                $this->Rollback();
            }
            throw $e;
        }
    }

}
