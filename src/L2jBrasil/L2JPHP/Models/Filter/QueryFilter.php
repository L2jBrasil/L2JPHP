<?php
/**
 * Created by PhpStorm.
 * User: Leonan
 * Date: 19/07/2018
 * Time: 16:54
 */

namespace L2jBrasil\L2JPHP\Models\Filter;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;

class QueryFilter
{
    public $_query = "";
    protected $_filter;
    private $_currentfilter;
    private $_tablealias = false;
    private $_tableModel;

    public function __construct(array $filter, AbstractBaseModel $modelInstance, $tableAlias)
    {

        $this->_filter = $filter;
        $this->_tableModel = $modelInstance;
        $this->_tablealias = ($tableAlias) ? $tableAlias : false;
        foreach ($this->_filter as $index => $this->_currentfilter) {
            $this->left();
            $this->campo();
            $this->condicao();
            $this->valor();
            $this->right();
            if (isset($this->_filter[$index + 1]) && !isset($this->_filter[$index + 1]->havingParameter)) {
                $this->operador();
            }
        }
    }


    private function left()
    {
        if (isset($this->_currentfilter->left)) {
            for ($i = 0; $i < $this->_currentfilter->left; $i++) {
                $this->_query .= "(";
            }
        }
    }

    private function campo()
    {
        if ($this->_tablealias) {
            /**
             *  Existe exceção quando é necessário passar o campo inteiro (já com apelido) como condição
             *  EX: T1.id => id é apelido de T1, que na verdade é de uma tabela secundária (JOIN).
             */
            $excecao = strstr($this->_currentfilter->col, '.');
            $personalizado = false;
            if (isset($this->_currentfilter->personalizado)) {
                $personalizado = true;
            }

            if ($excecao == false && $personalizado == false) {
                //if para checar se o campo não deve ter apelido
                if (isset($this->_currentfilter->noAlias) && $this->_currentfilter->noAlias) {
                    $this->_query .= " " . $this->_tableModel->translate($this->_currentfilter->col);
                } else {
                    $this->_query .= " " . $this->_tablealias . "." . $this->_tableModel->translate($this->_currentfilter->col);
                }
            } else {
                if ($excecao) {
                    $pieces = explode('.', $this->_currentfilter->col);
                    $pieces[0] = $this->_tableModel->translate($pieces[0]);
                    $this->_currentfilter->col = implode('.', $pieces);
                }
                $this->_query .= " " . $this->_tableModel->translate($this->_currentfilter->col);
            }
        } else {
            $this->_query .= " " . $this->_tableModel->translate($this->_currentfilter->col);
        }

        $this->_col[] = $this->_tableModel->translate($this->_currentfilter->col);
    }

    private function condicao()
    {
        if (!isset($this->_currentfilter->personalizado)) {
            $this->_query .= " " . $this->_currentfilter->condicao;
        }

    }

    private function valor()
    {
        $condicao = strtoupper($this->_currentfilter->condicao);


        switch ($condicao) {
            case 'BETWEEN':
                $valor0 = $this->formatByType($this->_currentfilter->valor[0]);
                $valor1 = $this->formatByType($this->_currentfilter->valor[1]);

                $this->_query .= " {$valor0} AND {$valor1}";
                break;

            case 'IN':
            case 'NOT IN':
                $in = "(";
                $c = 0;
                $t = count($this->_currentfilter->valor);
                $s = "";

                foreach ($this->_currentfilter->valor as $v) {
                    $s = ($c < ($t - 1)) ? "," : "";
                    $in .= $this->formatByType($v) . $s;
                    $c++;
                }

                $this->_query .= $in . ")";

                break;
            case 'LIKE':
                $lowerValor = strtolower($this->_currentfilter->valor);

                if (strpos($this->_currentfilter->valor, '%') !== false) {
                    $this->_query .= " '{$lowerValor}'";
                } else {
                    $this->_query .= " '%{$lowerValor}%' ";
                }

                break;
            default:
                $this->_query .= " " . $this->formatByType($this->_currentfilter->valor);
                break;
        }
    }

    protected function formatByType($valor)
    {
        if (strlen($valor) === 0) {
            return "''";
        }
        switch (strtoupper($valor)) {
            case "NOT NULL":
            case "NULL":
                return strtoupper($valor);
            default:
                return (!is_numeric($valor)) ? '"' . strtolower($valor) . '"' : $valor;
                break;
        }
    }

    private function right()
    {
        if (isset($this->_currentfilter->right)) {
            for ($i = 0; $i < $this->_currentfilter->right; $i++) {
                $this->_query .= ")";
            }
        }
    }

    private function operador()
    {
        if (isset($this->_currentfilter->operador)) {
            $this->_query .= " " . $this->_currentfilter->operador . " ";
        }
    }


    public function __toString()
    {
        return $this->_query;
    }


}