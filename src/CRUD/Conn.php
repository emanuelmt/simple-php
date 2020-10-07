<?php

/**
 * Conn.class [ CONEXÃO ]
 * Classe abstrata de conexão padrão SingleTon.
 * Retorna um objeto PDO pelo método estático getCoon();
 * 
 * @copyright (c) 2017, Emanuel Marques CREATIVE DESIGN PROJECTS
 */

namespace SimplePHP\CRUD;

class Conn {

    private static $Host;
    private static $Driver;
    private static $User;
    private static $Pass;
    private static $Dbsa;

    /** @var PDO */
    private static $Connect = null;

    /** @var PDO */
    protected $Conn;

    public function __construct(&$conn = false) {
        $this->Conn = $conn;
    }

    /**
     * Conecta com o banco de dados com o pattern Singleton.
     * Retorna um objeto PDO!
     */
    private static function Conectar() {
        try {
            if (self::$Connect == null) {
                $dsn = self::$Driver . ':host=' . self::$Host . ';dbname=' . self::$Dbsa . ';charset=utf8';
                $options = self::$Driver == 'mysql' ? [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'] : [];
                self::$Connect = new \PDO($dsn, self::$User, self::$Pass, $options);
            }
        } catch (\PDOException $e) {
            throw new \SimplePHP\Exception\ConnectionException("Não foi possível realizar a conexão com o base de dados.", $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new \Exception("Não foi possível realizar a conexão com o base de dados.", $e->getCode(), $e);
        }  
        self::$Connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return self::$Connect;
    }

    /** Retorna um objeto PDO Singleton Pattern. */
    public static function getConn($trasaction = false) {
        self::$Host = getenv("DBHOST");
        self::$Driver = getenv("DBDRIVER");
        self::$User = getenv("DBUSER");
        self::$Pass = getenv("DBPASS");
        self::$Dbsa = getenv("DBNAME");
        $conn = self::Conectar();
        if ($trasaction) {
            $conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
            $conn->beginTransaction();
        }
        return $conn;
    }

    public function Rollback() {
        if ($this->Conn->inTransaction()) {
            $this->Conn->rollback();
            $this->Conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        }
    }

    public function Commit() {
        if ($this->Conn->inTransaction()) {
            $this->Conn->commit();
            $this->Conn->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        }
    }

}
