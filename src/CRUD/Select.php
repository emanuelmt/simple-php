<?php

/**
 * <b>Read.class:</b>
 * Classe responsável por leituras genéricas no banco de dados!
 *
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace SimplePHP\CRUD;

class Select extends Conn {

    private $select;
    private $fields;
    private $table;
    private $where;
    private $group;
    private $order;
    private $limit;
    private $places;
    private $values;
    private $result;

    /** @var PDOStatement */
    private $statement;
    private $fetchMode = \PDO::FETCH_OBJ;

    /** @var PDO */
    protected $Conn;

    public function table($table) {
        if (empty($table)) {
            throw new \SimplePHP\Exception\ReadException("Informe a tabela para poder realizar a leitura em banco.");
        }
        $this->table = trim($table);
        return $this;
    }

    public function fields($fields) {
        if (empty(trim($fields))) {
            $this->fields = "*";
        } else {
            $this->fields = trim($fields);
        }

        return $this;
    }

    public function where($terms) {
        if (!empty(trim($terms))) {
            $this->where = trim($terms);
        }
        return $this;
    }

    public function group($terms) {
        if (!empty(trim($terms))) {
            $this->group = trim($terms);
        }
        return $this;
    }

    public function order($terms) {
        if (!empty(trim($terms))) {
            $this->order = trim($terms);
        }
        return $this;
    }

    public function limit($limit, $offset = 0) {
        if (!empty(trim($limit))) {
            $this->limit = [$limit, $offset];
        }
        return $this;
    }

    public function places($places = []) {
        if (!empty(trim($places))) {
            parse_str($places, $this->places);
        }

        return $this;
    }

    public function fetch($type = "object") {
        if ($type == "object") {
            $this->fetchMode = \PDO::FETCH_OBJ;
        } else {
            $this->fetchMode = \PDO::FETCH_ASSOC;
        }
        $this->setSelect();
        $this->setValues();
        $this->execute();
        return $this->result;
    }

    public function __invoke($select) {
        if (empty($select)) {
            throw new \SimplePHP\Exception\ReadException("Informe o comando de select para poder realizar a consulta no banco.");
        }
        $this->select = $select;
        return $this;
    }

    public function get() {
        return $this->result;
    }

    public function count() {
        return $this->statement->rowCount();
    }

    /**
     * **************************************************
     * **************** PRIVATE METHODS *****************
     * **************************************************
     */
    //Obtém o PDO e prepara a Query
    private function connect() {
        if (!$this->Conn) {
            $this->Conn = parent::getConn();
        }
        $this->statement = $this->Conn->prepare($this->select);
        $this->statement->setFetchMode($this->fetchMode);
    }

    //Cria a sintaxe da Query para Prepared Statements

    private function setSelect() {
        if (!$this->select) {
            $this->select = 'SELECT ';
            if (isset($this->fields)) {
                $this->select .= $this->fields . " FROM ";
            } else {
                $this->select .= "* FROM ";
            }
            if (empty($this->table)) {
                throw new \SimplePHP\Exception\ReadException("Informe a tabela para poder realizar a leitura em banco.");
            }
            $this->select .= $this->table;
            if ($this->where) {
                $this->select .= " WHERE " . $this->where;
            }
            if ($this->group) {
                $this->select .= " GROUP BY " . $this->group;
            }
            if ($this->order) {
                $this->select .= " ORDER BY " . $this->order;
            }
            if ($this->limit) {
                $this->select .= " LIMIT " . $this->limit[0] . " OFFSET " . $this->limit[1];
            }
        }
    }

    private function setValues() {
        if ($this->select) {
            $termsPlaces = [];
            preg_match_all('/(\{\{)([a-zA-Z0-9_ ]+)(\}\})/', $this->select, $matchs);
            if (isset($matchs[2])) {
                $termsPlaces = $matchs[2];
            }
            if ($termsPlaces) {
                if (!$this->places) {
                    throw new \SimplePHP\Exception\ReadException("Não foram informados os valores para os parâmetros do select.");
                }
                foreach ($termsPlaces as $param) {
                    if (!isset($this->places[$param])) {
                        throw new \SimplePHP\Exception\ReadException('Não foi informado o valor para o parâmetro {{' . $param . '}} do select.');
                    }
                }
                foreach ($this->places as $param => $value) {
                    $search = array_keys($termsPlaces, $param);
                    if (!$search) {
                        throw new \SimplePHP\Exception\ReadException('Não foi informado o parâmetro {{' . $param . '}} do select.');
                    }
                    $count = count($search);
                    for ($i = 1; $i <= $count; $i++) {
                        $this->select = preg_replace('/{{' . $param . '}}/', ":{$param}{$i}", $this->select, 1);
                        $this->values[":{$param}{$i}"] = $value;
                    }
                }
            } else if ($this->places) {
                throw new \SimplePHP\Exception\ReadException('Não foram informados os parâmetros do select.');
            }
        }
    }

    private function bindValues() {
        if ($this->values) {
            foreach ($this->values as $param => $value) {
                if ($param == ':limit' || $param == ':offset' || $param == ':LIMIT' || $param == ':OFFSET') {
                    $value = (int) $value;
                }
                $this->statement->bindValue($param, $value, ($value === null ? \PDO::PARAM_NULL : (is_int($value) ? \PDO::PARAM_INT : (is_bool($value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR))));
            }
        }
    }

    private function execute() {
        $this->connect();
        try {
            $this->bindValues();
            $this->statement->execute();
            $this->result = $this->statement->fetchAll();
        } catch (\PDOException $e) {
            if ($this->Conn->inTransaction()) {
                $this->Rollback();
            }
            throw $e;
        }
    }

}
