<?php

/**
 * <b>Delete.class:</b>
 * Classe responsável por deleções genéricas no banco de dados!
 *
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace SimplePHP\CRUD;

class Delete extends Conn {

    private $Tabela;
    private $Termos;
    private $Places;
    private $Result;

    /** @var PDOStatement */
    private $Delete;

    /** @var PDO */
    protected $Conn;

    /**
     * <b>ExeDelete:</b> Executa uma deleção simplificada no banco de dados utilizando prepared statements.
     * Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor!
     * @param type $Tabela
     * @param type $Termos
     * @param type $ParseString
     */
    public function ExeDelete($Tabela, $Termos, $ParseString) {
        $this->Tabela = (string) $Tabela;
        $this->Termos = (string) $Termos;

        parse_str($ParseString, $this->Places);
        $this->getSyntax();
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getRowCount() {
        return $this->Delete->rowCount();
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
        $this->Delete = $this->Conn->prepare($this->Delete);
    }

    //Cria a sintaxe da Query para Prepared Statements
    private function getSyntax() {
        $this->Delete = "DELETE FROM {$this->Tabela} {$this->Termos}";
    }

    private function Execute() {
        $this->Connect();
        try {
            $this->Delete->execute($this->Places);
            $this->Result = [true];
        } catch (\PDOException $e) {
            if ($this->Conn->inTransaction()) {
                $this->Rollback();
            }
            throw $e;
        }
    }

}
