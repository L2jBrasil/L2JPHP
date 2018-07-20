<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Filter;


class FilterFactory
{

    public $_currentFilter = null;
    private $_filterarray = [];
    private $_me;

    public function __construct($arrayFilter = [])
    {
        if (count($arrayFilter) > 0) {
            foreach ($arrayFilter as $filterObject) {
                if (is_object($filterObject)) {
                    if (!$filterObject instanceof FilterObject) {
                        $filterObject = $this->converttoFilterObject($filterObject);
                    }
                    array_push($this->_filterarray, $filterObject);
                }
            }
        }
        $this->_me = $this;
    }

    public function convertToFilterObject(\stdClass $object)
    {
        $datafields = null;
        $condicao = null;
        $valor = null;
        $type = "string";
        $operador = null;
        $left = 0;
        $right = 0;
        $jsonfield = null;

        $array_ = get_object_vars($object);
        extract($array_, EXTR_IF_EXISTS);
        return new FilterObject($datafields, $condicao, $valor, $type, $operador, $left, $right, $jsonfield);
    }

    /**
     *
     * @param type $datafiled
     * @param type $condicao
     * @param type $valor
     * @param type $type
     * @param type $operador
     * @param type $left
     * @param type $right
     * @param type $jsonfield
     * @return FilterObject;
     */
    public function add($datafiled, $condicao, $valor, $type = "string", $operador = null, $left = 0, $right = 0, $jsonfield = null)
    {
        $me = $this->_me;
        $this->_currentFilter = new FilterObject($datafiled, $condicao, $valor, $type, $operador, $left, $right, $jsonfield);
        array_push($this->_filterarray, $this->_currentFilter);

        /**
         * NOTA:
         * Não tente fazer isso em casa, a não se que você saiba exatamente o que está fazendo.
         * Para adicionar a possibilidade de chamar os métodos add e get da classe FilterFactory
         * dentro do _currentFilter (instância de  FilterObject) foi adicionado um __call especial que permite isso.
         * Não é uma técnica bonita nem segura para se fazer por ai, a não ser que você realmente saiba o que está fazendo.        *
         *
         */

        $this->_currentFilter->add = function ($datafiled, $condicao, $valor, $type = "string", $operador = null, $left = 0, $right = 0, $jsonfield = null) use ($me) {
            return $me->add($datafiled, $condicao, $valor, $type, $operador, $left, $right, $jsonfield);
        };
        $this->_currentFilter->get = function () use ($me) {
            return $me->get();
        };

        return $this->_currentFilter;
    }

    public function get()
    {
        /*
         * É preciso fazer um Clean-up pois os métodos adicionados vão deixar o objeto bem sujo
         * e não há necessidade que eles permaneçam após o get.
         * Porém esse recurso destruirá a instância
         */
        array_map(function ($obj) {
            //Se no futuro for preciso aproveitar o objeto após o get, a solução é setar para null, porém a propriedade ainda existe.
//            $obj->add = null;
//            $obj->get = null;
            unset($obj->add, $obj->get);
        }, $this->_filterarray);

        return $this->_filterarray;
    }

    public function addFromObject(\stdClass $object)
    {
        $this->_currentFilter = $this->converttoFilterObject($object);
        array_push($this->_filterarray, $this->_currentFilter);
    }

}