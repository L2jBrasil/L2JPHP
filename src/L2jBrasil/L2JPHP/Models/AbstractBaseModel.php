<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models;


use Exception;
use L2jBrasil\L2JPHP\Models\Filter\QueryFilter;
use PDO;
use UnderflowException;

abstract class AbstractBaseModel extends AbstractSQL
{

    protected $_table;
    protected $_primary;

    protected $_tableMap = [];
    protected $_passencodemethod = "pack"; //TODO Create constant


    /**
     * Retorna todos os registros
     * @param array $cols
     * @param array|null $filter
     * @param bool $limit
     * @param bool $order
     * @param string $orderDirection
     * @return mixed
     */
    public function all($cols = ['*'], array $filter = null, $limit = false, $order = false, $orderDirection = "DESC")
    {

        $cols = $this->translateAll($cols);


        $sql = $this->select($cols);

        if ($filter) {
            $where = new QueryFilter($filter, $this);
            $sql->where((string)$where);
        }

        if ($order) {
            $order = $this->translate($order) . ' ' . $orderDirection;
            $sql->orderby($order);
        }

        if ($limit) {
            $sql->limit($limit);
        }


        $result = $sql->query()->FetchAll();

        return $this->translateDataObj($result, true); //"Flip" back the result to harmonized structure
    }

    /**
     * Retorna um registro pela chave primária
     * @param $id
     * @return $this
     */
    public function get($id, $cols = ['*'])
    {
        $cols = $this->translateAll($cols);

        return $this->select($cols)
            ->where("{$this->_primary} == {$id}")
            ->query()
            ->Fetch();
    }

    /**
     * @param type $id
     * @return type|mixed
     */
    public function delete($id)
    {
        return parent::delete($id);
    }

    /**
     * Update based on primary key
     * @param $id
     * @param $data
     * @return bool|AbstractSQL|mixed
     */
    public function update($dados,$id)
    {
        $dados = $this->translateDataObj((array) $dados);
        $where = "{$this->_primary} = '{$id}''";
        return parent::update($dados, $where);
    }

    /**
     * Insert new data
     * @param $dados
     * @return int
     */
    public function insert($dados)
    {
        $dados = $this->translateDataObj($dados);
        return parent::insert($dados);
    }

    /**
     * id -> charId
     * name -> char_name, etc...
     * Traduz uma coluna de um model
     * @param $colName
     * @param bool $reverse
     * @return mixed
     */
    public function translate($colName, $reverse = false)
    {
        $dictionary = ($reverse) ? array_flip($this->_tableMap) : $this->_tableMap;

        if (array_key_exists($colName, $dictionary)) {
            return $this->_tableMap[$colName];
        }
        return $colName;
    }

    /**
     * Traduiz todas as colunas de um model
     * @param array $colums
     * @return array
     */
    public function translateAll(array $colums, $reverse = false)
    {
        if ($reverse) {
            $modelInstance = $this;
            return array_map(function ($colName) use ($modelInstance) {
                return $modelInstance->translate($colName, true);
            }, $colums);

        } else {
            return array_map(array($this, 'translate'), $colums);
        }

    }


    /**
     * @param array $data
     * @return array
     */
    public function translateDataObj(array $dados, $reverse = false)
    {
        $translated = [];
        foreach ($dados as $key => $value) {
            if(is_object($value)||is_array($value)){
                $translated[$key] = $this->translateDataObj($value,$reverse);
            }else{
                $translated[$this->translate($key, $reverse)] = $value;
            }

        }
        return $translated;
    }

    /**
     * Abtract function to check if value exist by given column
     * @param $col
     * @param $value
     * @return mixed
     *
     */
    public function exists($col, $value)
    {
        $col = $this->translate($col);
        $value = $this->quote($value);

        return $this->select($col)
            ->where("{$col} = {$value}")
            ->query()
            ->Fetch();
    }

    /**
     * Abtract function to check if value is unique
     * @param $col
     * @param $value
     * @return mixed
     */
    public function checkUnique($col, $value)
    {
        $col = $this->translate($col);
        $value = $this->quote($value);


        return $this->select([$col, "count(*) as Count"])
            ->where("{$col} = {$value}")
            ->groupby($col)
            ->having("count(*) > 1")
            ->query()
            ->Fetch();
    }


    /**
     * @param $string
     * @param int $parameter_type
     * @return string
     * @throws Exception
     *
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        $conn = $this->getDB()->getConn()->connection();
        return $conn->quote($string);
    }


    /**
     * Return encoded password
     * @param $password
     * @return string
     */
    public function encodepwd($password)
    {
        switch ($this->_passencodemethod) {
            case "pack"://l2jserver
                return base64_encode(pack('H*', sha1(trim($password))));
            case "whirlpool":
                return base64_encode(hash('whirlpool', trim($password), true));
            case "sha1": //L2jMobius
                return base64_encode(sha1($password, true));
            default:
                throw new UnderflowException("Not Imlemented encode {$this->_passencodemethod} ");

        }

    }


}