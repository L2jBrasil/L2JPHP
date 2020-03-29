<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Connection;


use BadMethodCallException;
use Error;
use Exception;
use PDO;
use PDOException;

class PDOFactory
{

    protected $dsn;
    protected $username;
    protected $password;
    protected $pdo;
    protected $driver_options;
    private $connectionID;

    public function __construct($dsn, $username = "", $password = "",
                                array $driver_options = [PDO::ATTR_TIMEOUT, '5',
                                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                                    PDO::ATTR_EMULATE_PREPARES => true,
                                    PDO::ATTR_STRINGIFY_FETCHES => false])
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->driver_options = $driver_options;
    }

    public function disconnect()
    {
        $this->pdo = null;
    }

    public function getDB()
    {
        $this;
    }

    public function getConnectionID()
    {
        return $this->connectionID;
    }

    public function __destruct()
    {
        //TODO: Avaliar impactos de forçar destrução da conexão PDO
        //$this->disconnect();
    }

    public function __sleep()
    {
        return array('dsn', 'username', 'password', 'driver_options');
    }

    public function __wakeup()
    {
        $this->connect();
    }

    public function connect()
    {
        try {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->driver_options);

            if (!$this->pdo) {
                throw new Exception("DB Connection failure");
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, 600);
            $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, 1);

            $this->connectionID = $this->pdo->query('SELECT CONNECTION_ID()')->fetch(PDO::FETCH_COLUMN);


            return $this->pdo;


        } catch (PDOException | Exception | Error  $e) {
            throw new Exception("DB Connection failure", $e->getCode(), $e);
        }
    }

    public function __call($method, array $arguments)
    {
        try {
            $this->connection()->query("SELECT 1;")->execute();
        } /* catch (\ErrorException $e) { // https://secure.php.net/manual/pt_BR/language.errors.php7.php
            if (!stristr($e->getMessage(), 'Error while sending')) {
                throw $e;
            }
            $this->reconnect();
        }//https://secure.php.net/manual/pt_BR/class.error.php
        /*catch (\Error $e){
            Apenas php7
        }
        */
        catch (Exception $e) {

            switch (true) {
                case (strpos($e->getMessage(), 'server has gone away') !== false):
                case (strpos($e->getMessage(), 'Error while sending') !== false):
                    usleep(5000); //Espera 5ms
                    $this->reconnect();
                    $this->connection()->query("SELECT 1;")->execute();
                    break;
                default:
                    throw $e;
            }

        }

        //Magicamente torna o Factory num decorator para o PDO :)
        if (method_exists($this->connection(), $method)) {
            return call_user_func_array([$this->connection(), $method], $arguments);
        } else if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        } else {
            throw  new BadMethodCallException("Undefined method '{$method}' in " . __NAMESPACE__ . __CLASS__);
        }

        return call_user_func_array(array($this->connection(), $method), $arguments);
    }

    protected function connection()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        return $this->pdo instanceof PDO ? $this->pdo : $this->connect();
    }

    public function reconnect()
    {
        $this->pdo = null;
        return $this->connect();
    }


}