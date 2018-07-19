<?php
/**
 * Created by PhpStorm.
 * User: Leonan
 * Date: 19/07/2018
 * Time: 17:28
 */

namespace L2jBrasil\L2JPHP\Models\Filter;


class FilterObject extends \stdClass
{
    /**
     * Indica o nome da coluna, ele pode ter alias ou não,
     *  se não tiver alias irá assumir o que for passado no construtor da class:
     * @var string
     */
    public $datafields;
    /**
     * pode ser os seguintes: char,int, decimal, money, date, memo
     *  Convenções:
     *  - bool: Sempre convertido para valor inteiro (0,1)
     *  - cpf, CNPJ, cpf: remove caracter especial, deixando apenas números
     *  - valor default: Se não for irá tratar como string adicionando aspas ao redor.
     * @var string
     */
    public $tipo = "string";
    /**
     * pode ser qualquer operador do mysql: =, <>, <, > (...)
     *  Convenções:
     *  - BETWEEN : O parametro $valor deve ser um array com 2 posições.
     *  - IS : O parâmetro valor deve ser NOT NULL, NULL.
     *  - IN  & NOT IN : O parâmetro valor deve ser um array de inteiros.
     *  - LIKE : O valor deverá conter % no inicio ou no fim, se não definido
     *           a classe colocará % ao redor do valor informado.
     * @var string
     */
    public $condicao;
    /**
     * O dado em si
     * @var string
     */
    public $valor;
    /**
     * indica a quantidade dê parênteses à esquerda
     * @var int
     */
    public $left = 0;
    /**
     * indica a quantidade dê parênteses à direita
     * @var int
     */
    public $right = 0;
    /**
     * AND ou OR
     * @var type
     */
    public $operador = null;
    /**
     * É usado para quando o valor buscado está dentro de um json (coluna tipo json do MYSQL)
     * @var string
     */
    public $jsonfield = null;

    public function __construct($datafiled, $condicao, $valor, $type = "string", $operador = null, $left = 0, $right = 0, $jsonfield = null)
    {
        $this->datafields = $datafiled;
        $this->tipo = $type;
        $this->condicao = $condicao;
        $this->valor = $valor;
        $this->left = $left;
        $this->right = $right;

        if (null !== $operador) {
            $this->operador = $operador;
        }

        if (null !== $jsonfield) {
            $this->jsonfield = $jsonfield;
        }
    }

    public function set($key, $value)
    {
        if (!preg_match('/(datafields|tipo|condicao|valor|datafields|left|right|operador|jsonfield)/i', trim($key))) {
            throw new \InvalidArgumentException("Invalid Property {$key}");
        }
        $this->$key = $value;
        return $this;
    }


    public function addleft()
    {
        $this->left++;
        return $this;
    }

    public function addright()
    {
        $this->right++;
        return $this;
    }

    public function removeleft()
    {
        if ($this->left > 0) {
            $this->left--;
        }
        return $this;
    }

    public function removeright()
    {
        if ($this->right > 0) {
            $this->right--;
        }
        return $this;
    }

//
//    http://stackoverflow.com/questions/2938004/how-to-add-a-new-method-to-a-php-object-on-the-fly
//    NO php7: http://php.net/manual/en/function.runkit-method-add.php
//
    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }


}
