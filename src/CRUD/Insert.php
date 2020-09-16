<?php

/**
 * <b>Create.class:</b>
 * Classe responsável por cadastros genéticos no banco de dados!
 *
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace App\Conn;

class Insert extends Conn {

    private $Tabela;
    private $Dados;
    private $Result;
    private $LastInsert;
    private $Error;

    /** @var PDOStatement */
    private $Create;

    /** @var PDO */
    protected $Conn;

    /**
     * <b>ExeInsert:</b> Executa um cadastro simplificado no banco de dados utilizando prepared statements.
     * Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor!
     *
     * @param STRING $Tabela = Informe o nnome da tabela no banco!
     * @param ARRAY $Dados = Informe um array atribuitivo. (Nome da coluna => Valor).
     */
    public function ExeInsert($Tabela, array $Dados, $LastInsert = false) {
        $this->Tabela = (string) $Tabela;
        $this->Dados = $Dados;
        foreach ($Dados as &$value) {
            if ($value == '')
                $value = null;
        }
        $this->getSyntax();
        if (DRIVER == "firebird") {
            $this->LastInsert = $LastInsert;
            if ($LastInsert) {
                $this->Create .= " RETURNING $LastInsert";
            }
        }
        $this->Execute();
    }

    public function getResult() {
        return $this->Result;
    }

    public function getError() {
        return $this->Error;
    }

    public function getLastInsert() {
        return $this->LastInsert;
    }

    /**
     * **************************************************
     * **************** PRIVATE METHODS *****************
     * **************************************************
     */
    private function Connect() {
        if (!$this->Conn) {
            $this->Conn = parent::getConn();
        }
        $this->Create = $this->Conn->prepare($this->Create);
        foreach ($this->Dados as $Key => $Value) {
            $this->Create->bindValue(':' . $Key, $Value, empty($Value) ? \PDO::PARAM_NULL : (is_int($Value) ? \PDO::PARAM_INT : (is_bool($Value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR)));
//            $field = explode("_", $Key);
//            if (strtoupper($field[0]) == "CD") {
//                $this->Create->bindValue(':' . $Key, (int) $Value, \PDO::PARAM_INT);
//            }else if($Key == "DS_SENHA"){
//                $this->Create->bindValue(':' . $Key, $Value, empty($Value) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
//            } else {
//                $Value = $Value == "NULL" ? null : $Value;
//                $this->Create->bindValue(':' . $Key, mb_strtoupper($Value), empty($Value) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
//            }
        }
    }

    private function getSyntax() {
        $Fields = implode(', ', array_keys($this->Dados));
        $Places = ':' . implode(', :', array_keys($this->Dados));
        $this->Create = "INSERT INTO {$this->Tabela} ({$Fields}) VALUES ({$Places})";
    }

    private function Execute() {
        $this->Connect();
        try {
            $this->Create->execute();
            if (DRIVER == 'firebird' && $this->LastInsert) {
                $this->LastInsert = $this->Create->fetch(\PDO::FETCH_ASSOC)[$this->LastInsert];
            } else if (DRIVER == 'mysql') {
                $this->LastInsert = $this->Conn->lastInsertId();
            }
            $this->Result = true;
        } catch (\PDOException $e) {
            if ($this->Conn->inTransaction()) {
                $this->Rollback();
            }
            throw $e;
        }
    }

}
