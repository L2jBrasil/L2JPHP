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


        return $sql->query()->FetchAll();
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
    public function update($id, array $dados)
    {
        $dados = $this->translateDataObj($dados);
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
     * @return mixed
     */
    public function translate($colName)
    {
        if (array_key_exists($colName, $this->_tableMap)) {
            return $this->_tableMap[$colName];
        }
        return $colName;
    }

    /**
     * Traduiz todas as colunas de um model
     * @param array $colums
     * @return array
     */
    public function translateAll(array $colums)
    {
        return array_map(array($this, 'translate'), $colums);
    }


    /**
     * @param array $data
     * @return array
     */
    public function translateDataObj(array $dados)
    {
        $translated = [];
        foreach ($dados as $key => $value) {
            $translated[$key] = $this->translate($key);
        }
        return $translated;
    }
}