<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models;


use L2jBrasil\L2JPHP\Models\Filter\QueryFilter;

abstract class AbstractBaseModel extends AbstractSQL
{

    protected $_table;
    protected $_primary;

    protected $_tableMap = [];


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
                $translated[$key] = $this->translate($key, $reverse);
            }

        }
        return $translated;
    }
}