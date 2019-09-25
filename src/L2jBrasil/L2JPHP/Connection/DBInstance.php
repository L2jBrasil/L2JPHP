<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */


namespace L2jBrasil\L2JPHP\Connection;


use L2jBrasil\L2JPHP\ConfigSet;

class DBInstance
{
    /**
     * @var Singleton
     */
    private static $instance;

    /**
     * Conexão com o banco de dados
     * @var PDOFactory
     */
    private static $connection;

    /**
     * @var string
     */
    private static $_dbname = null;
    /**
     * @var string
     */
    private static $_host = null;
    /**
     * @var integer
     */
    private static $_port = null;
    /**
     * @var string
     */
    private static $_user = null;
    /**
     * @var string
     */
    private static $_pwd = null;
    /**
     * @var string
     */
    private static $_driver = null;

    /**
     * @var ConfigSet|null
     */
    private static $configSet = null;


    /**
     * Singleton constructor.
     */
    private function __construct(ConfigSet $configSet)
    {
        register_shutdown_function(array(&$this, 'FatalErrorCatch'));
        self::$_dbname = $configSet->_dbName;
        self::$_host = $configSet->_dbHost;
        self::$_port = $configSet->_dbPort;
        self::$_user = $configSet->_dbUser;
        self::$_pwd = $configSet->_dbPwd;
        self::$_driver = $configSet->_dbDriver;
        self::$configSet = $configSet;

    }

    /**
     * Prepara a SQl para ser executada posteriormente
     * @param $sql
     * @return \PDOStatement
     */
    public static function prepare($sql)
    {

        return self::getConn()->prepare($sql);
    }

    /**
     * Retorna a conexão PDO com o banco de dados
     * @param bool $renew força a renovação da conexão
     * @return PDOFactory
     */
    public static function getConn($renew = false)
    {
        if ($renew) {
            self::$connection == null;
        }

        if (!(self::$connection == null || !(self::$connection instanceof PDOFactory))) {
            $dsn = self::$_driver . ":host=" . self::$_host . self::$_port . ";dbname=" . self::$_dbname;
            self::$connection = new PDOFactory($dsn, self::$_user, self::$_pwd);
            self::$connection->connect();

            if (self::$connection) {
                //Permitir uma conexão sem database especificada
                if (self::$_dbname !== null) {
                    self::selectDatabase(self::$_dbname);
                }
            } else {
                throw new \RuntimeException("Unable to connect to primary database.", 500, null);
            }
        }


        return self::$connection;
    }

    /**
     * Muda o banco de dados selecionado (multi-db support)
     * @param $dbname
     */
    public static function selectDatabase($dbname)
    {
        if (self::$_dbname != $dbname) {
            self::$_dbname = $dbname;
            self::exec("USE {$dbname};");
        }
    }

    public static function selectedDatabaseName()
    {
        return self::$_dbname;
    }

    /**
     * @param $sql
     * @return mixed
     */
    public static function exec($sql)
    {
        return self::getConn()->exec($sql);
    }

    /**
     * @return int
     */
    public static function lastInsertId()
    {
        return self::$connection->lastInsertId();
    }

    /**
     * Inicia uma transação
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getConn()->beginTransaction();
    }

    /**
     * Comita uma transação
     * @return bool
     */
    public static function commit()
    {
        return self::getConn()->commit();
    }

    /**
     * Realiza um rollback na transação
     * @return bool
     */
    public static function rollBack()
    {
        return self::getConn()->rollBack();
    }

    public static function close()
    {
        self::$connection = null;
        $configSet = self::$configSet;
        Container::save($configSet::hash(),null);
        self::$instance = null;
    }

    /**
     * Work Around:
     * Função pra capturar fatal error na conexão com o banco de dados.
     * Por algum motivo quando o banco de dados está desligado ou responde com um timeout muito grande (firewall possivelmente)
     * o erro gerado então é um user-level e user-level errors não podem ser "capturados";
     *
     * As funções que podem ser utilizadas aqui são muito limitadas
     */
    public static function FatalErrorCatch()
    {
        try {
            $error = error_get_last();
            //Ignorar quando for algum desses erros
            $byPass = array(E_RECOVERABLE_ERROR,
                E_WARNING,
                E_PARSE,
                E_NOTICE,
                E_STRICT,
                E_DEPRECATED,
                E_CORE_WARNING
            );


            //Verifica se é um Fatal error
            if ($error !== NULL && is_array($error) && array_key_exists('type', $error) && !in_array($error['type'], $byPass)) {

                die(self::safeString($error['message']) . " in " . $error['file'] . "[" . $error['line'] . "]");
            }
        } catch (\Exception $e) {
            die(self::safeString($e->getMessage()) . " in " . $e->getFile() . "[" . $e->getLine() . "]" . PHP_EOL . self::safeString($e->getTraceAsString()));
        }
    }

    /**
     * Oculta a senha de qualquer string que eventualmente possa estar sendo exibida
     * @param $str
     * @return mixed
     */
    private static function safeString($str)
    {
        return str_replace(self::$configSet->_dbPwd, "**********", $str);
    }

    /**
     * @param $chrMethod
     * @param $arrArguments
     * @return mixed
     */
    final public static function __callStatic($chrMethod, $arrArguments)
    {

        $objInstance = self::getInstance(self::$configSet);
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
    }

    /**
     * @param bool $renew
     * @return Singleton
     */
    public static function getInstance(ConfigSet $configSet, $renew = false)
    {
        $hash = $configSet::hash();
        if (!self::$instance || $renew) {
            Container::save(self::$configSet, new DBInstance($configSet));
            self::$instance = null;
        }
        self::$instance = Container::get($hash);
        return self::$instance;
    }

    final public function __wakeup()
    {
        self::testConn();
    }

    public static function testConn()
    {
        try {
            self::getConn()->query("SELECT 1;")->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }
            self::reconnect();
        }
    }

    /**
     *  Refaz a conexão de forma segura
     */
    public static function reconnect()
    {
        if (!self::$connection->inTransaction()) {
            gc_enable();
            self::$connection = null;
            gc_collect_cycles();
            self::$connection = self::getConn(true);
        } else {
            throw new \RuntimeException("Can't Reconnect with active transactions");
        }
    }

    public function __destruct()
    {
        //self::$connection = null;
        //self::$instance = null;
    }

    final public function __sleep()
    {
        return array('_logloading', '_dbname', '_host', '_port', '_user', '_driver', '_trycreatedb');
    }

}