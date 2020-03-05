<?php
/**
 * Created by PhpStorm.
 * User: Leonan
 * Date: 19/07/2018
 * Time: 15:40
 */

namespace L2jBrasil\L2JPHP\Models;


use Application\Util;
use BadMethodCallException;
use Exception;
use L2jBrasil\L2JPHP\ConfigSet;
use L2jBrasil\L2JPHP\Connection\DBInstance;
use PDOStatement;
use RuntimeException;
use stdClass;

class AbstractSQL
{


    private  $config = null;
    protected $_table;
    protected $_primary = "id";

    protected $_group = false;
    protected $_order = false;
    protected $_limit = false;
    protected $_having = false;
    protected $_softdelete = true;
    private $_dbname;
    private $_host;
    private $_user;
    private $_pwd;
    private $_driver;
    private $_db;
    private $_stmt;
    private $_sql = "";
    private $_where = array();
    private $_join = array();
    private $_mysqlilink = null;
    private $_tableCols = null;
    private $_tableSchema = null;

    /**
     * AbstractSQL constructor.
     * @param ConfigSet|null $configset
     */
    public function __construct(ConfigSet $configset = null)
    {
        if(!$configset){
            $configset = ConfigSet::getDefaultInstance();
        }
        $this->config = $configset;
    }


    public function getConfigSet()
    {
        return $this->config;
    }

    /**
     * @return null
     */
    public function getSchema()
    {
        if ($this->_tableSchema == null) {
            $db = $this->getDB();
            $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS "
                . "WHERE table_name = '" . $this->_table . "' and table_schema = '" . $db->selectedDatabaseName() . "'";

            $dados = $this->query($sql);
            $this->_tableSchema = $this->FetchAll($dados);
        }
        return $this->_tableSchema;
    }

    /**
     * @return DBInstance
     */
    public function getDB()
    {
        if (!$this->_db && empty($this->_db)) {
            $this->_db = $this->getDbEnginer();
        }
        return $this->_db;
    }

    /**
     * @return Singleton
     */
    private function getDbEnginer()
    {
        return DBInstance::getInstance($this->config);
    }

    /**
     * Executa a SQL contruida
     *
     * @param null $sql
     * @param null $dados
     * @return $this
     */
    public function query($sql = null, $dados = null)
    {
        if (!$sql) {
            $sql = $this->getSql();
        }
        $db = $this->getDB();
        $this->_stmt = $db->prepare($sql);
        $this->cleanQuery();
        $this->_stmt->execute($dados);

        return $this;
    }

    /**
     * Retorna a string SQL
     * @return string
     */
    public function getSql()
    {
        if (count($this->_join) > 0) {
            foreach ($this->_join as $joins) {
                $this->_sql = $this->_sql . $joins;
            }
        }
        if (count($this->_where) > 0) {
            $this->_sql = $this->_sql . " WHERE ";
            foreach ($this->_where as $condicoes) {
                $this->_sql = $this->_sql . $condicoes;
            }
        }
        if ($this->_group && $this->_group != "") {
            $this->_sql = $this->_sql . " GROUP BY " . $this->_group;
        }
        if ($this->_having && $this->_having != "") {
            $this->_sql = $this->_sql . " HAVING " . $this->_having;
        }
        if ($this->_order && $this->_order != "") {
            $this->_sql = $this->_sql . " ORDER BY " . $this->_order;
        }
        if ($this->_limit && $this->_limit != "") {
            $this->_sql = $this->_sql . " LIMIT " . $this->_limit;
        }
        return $this->_sql;
    }

    /**
     *  Limpa as variáveis do query builder
     */
    public function cleanQuery()
    {
        //Zero as variáveis de apoio
        $this->_join = array();
        $this->_order = false;
        $this->_limit = false;
        $this->_where = array();
        $this->_having = false;
        $this->_sql = null;
        $this->_group = false;
    }

    /**
     * @param null $fechMode
     * @return mixed
     */
    public function FetchAll($fechMode = null)
    {
        if (null != $fechMode && is_integer($fechMode)) {
            $this->_stmt->setFetchMode($fechMode);
        }
        $result = $this->_stmt->fetchAll();
        $this->_stmt->closeCursor();
        $this->_stmt = null;
        return $result;
    }

    /**
     * @return mixed
     */
    public function FetchColumn()
    {
        $result = $this->_stmt->fetchColumn();
        $this->_stmt->closeCursor();
        $this->_stmt = null;
        return $result;
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->getDB()->rollBack();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->getDB()->commit();
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getDB()->beginTransaction();
    }

    /**
     * @param $dbname
     */
    public function selectDatabase($dbname)
    {
        return $this->getDB()->selectDatabase($dbname);
    }

    /**
     * @param $sql
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        return $this->getDB()->prepare($sql);
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function exec($sql)
    {
        return $this->getDB()->exec($sql);
    }

    /**
     * @return mixed
     */
    public function closeStmtCursor()
    {
        return $this->_stmt->closeCursor();
    }

    /**
     * @return mixed
     */
    public function closeCursor()
    {
        return $this->_stmt->closeCursor();
    }

    /**
     * Insere um registro
     * @param $dados
     * @return int
     */
    public function insert($dados)
    {
        if (!$this->_table) {
            throw new RuntimeException("The model's table is not defined");
        }

        $valores = array();
        $sql = $this->gemInsertString($this->_table, $dados, $valores);

        $this->query($sql, $valores);


        $lastInsertId = $this->getDB()->lastInsertId();


        if ($lastInsertId === "0") {
            $lastInsertSql = $this->select('LAST_INSERT_ID() as id')->getSql();
            $lastInsertId = $this->query($lastInsertSql)->Fetch()["id"];
        }

        return $lastInsertId;
    }

    /**
     * Função que constrói instrução insert ou insert com on duplicate update
     * INSERT (IGNORE) INTO `{$tabela}` ({$keys}) VALUES ({$values})
     * (ON DUPLICATE KEY  UPDATE " . implode(',', $updateCols))
     * @param type $tabela
     * @param type $dados
     * @param type $valores
     * @param type $updateDuplicate
     * @param type $updateIgnoreKeys
     * @return string
     */
    public function gemInsertString($tabela, $dados, &$valores, $updateDuplicate = false, $updateIgnoreKeys = [], $ignore = false)
    {
        $arraydados = $this->sanitize($dados, $tabela);

        $cols = [];
        $vals = [];
        $updateCols = [];
        $updateVals = [];

        foreach ($arraydados as $c => $value) {

            if ($this->formatValue($value)) {
                $v = $value;
            } else {
                $v = "?";
                $valores[] = $value;
            }
            $cols[] = "`{$c}`";
            $vals[] = $v;


            if ($updateDuplicate && !in_array($c, $updateIgnoreKeys)) {
                $updateCols[] = "`{$c}` = {$v}";
                if ($v == "?") {
                    $updateVals[] = $value;
                }
            }
        }


        $keys = implode(",", $cols);
        $values = implode(",", $vals);
        $sql = "INSERT" . (($ignore) ? " IGNORE " : "") . " INTO `{$tabela}` ({$keys}) VALUES ({$values})";

        if ($updateDuplicate && count($updateCols) > 0) {
            $sql .= " ON DUPLICATE KEY  UPDATE " . implode(',', $updateCols);
        }

        $sql .= ";";

        return $sql;
    }

    /**
     * Remove do objeto de dados, aquelas colunas que não pertencem à tabela
     * @param $dados
     * @param bool $tabela
     * @return array
     */
    public function sanitize(array $dados)
    {
        $colunas = get_object_vars($this->getColumns());


        if (count($colunas) == 0) {
            return $dados;
            throw new Exception("Select must be granted to site user to INFORMATION_SCHEMA");
        }
        return array_intersect_key($dados, $colunas);
    }

    /**
     * Retorna objeto com todas as colunas e os valores padrões de cada uma
     * Formato chave/valor
     * @return null|stdClass
     */
    public function getColumns()
    {
        if ($this->_tableCols == null) {
            $sql = "SELECT COLUMN_NAME, COLUMN_COMMENT, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS "
                . "WHERE table_name = '" . $this->_table . "' and table_schema = '" . $this->_dbname . "'";

            $colunas = $this->query($sql)->FetchAll();

            $obj = new stdClass();
            //Cria o objeto chave valor conforme expecificação
            foreach ($colunas as $value) {
                $col = $value['COLUMN_NAME'];
                $obj->$col = $value['COLUMN_DEFAULT'];
            }
            $this->_tableCols = $obj;
        }
        return $this->_tableCols;
    }

    /**
     * Função detecta se o valor passado para query é uma função.
     * Além disso ela modifica o valor do @param $value corrigindo-o para um formato que seja possível ser processado
     * pelo prepare do PDO.
     * @note:
     * Porque não posso passar funções como valores para o prepare do PDO?
     * https://stackoverflow.com/questions/4259274/why-cant-you-pass-mysql-functions-into-prepared-pdo-statements
     * @param $value
     * @return bool
     */
    private function formatValue(&$value)
    {
        //TODO: Essa função pode ser usada para validar SQL-Injections

        /**
         * Se o valor passado for um objeto ou array, deve ser alterado para o formato JSON (string), logo
         * esse valor não é uma função.
         */
        if (is_object($value) || is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); //Aproveito pra tratar automaticamente o campo json
            return false;
        }


        /**
         * Palavras que não devem ser escapadas e não são funções.
         * As palavras colocadas aqui serão buscada exatamente como está aqui.
         * De forma insensível ao case da letra.
         */
        $wordBlacklist = array_map(function ($val) {
            //Verifica se é uma variável com @ no começo, nesse caso o deve ficar fora do boundary: https://pt.stackoverflow.com/questions/220000/n%C3%A3o-consigo-utilizar-boundary-b-para-validar-uma-palavra-que-comece-com
            if (preg_match("#^@#", $val)) {
                $fragments = explode("@", $val);
                return "^@?\b{$fragments[1]}\b";
            }

            return "^\b{$val}\b"; //Adiciona o boundary https://pt.stackoverflow.com/questions/110874/pra-que-serve-um-boundary-b-numa-express%C3%A3o-regular
        },
            ['@userid', 'IS NULL', 'IS NOT NULL', 'CURRENT_TIMESTAMP']
        );

        /**
         * Funções, que tenham ( logo após seu nome. Se tiver 2 espaços não vai pegar.
         */
        $functionBlacklist = array_map(function ($val) {
            return "^\b{$val}\b\s*\(?.*(\)|;)$"; //Adiciona o boundary porém adiciona a validação da abertura de parênteses
        },
            //Não usar aqui DROP, SELECT, ou outras funções que possam causar injection.
            ['LOAD_FILE', 'sql_to_decimal', 'COALESCE', 'getVersaoEO', 'getPessoaById', 'CONVERT',
                'ST_GeomFromText', 'ST_AsGeoJSON', 'ST_GeomFromGeoJSON', 'ST_GEOMETRYTYPE', 'ST_AsText', 'CONCAT_WS', 'CONCAT',
                'json_extract', 'JSON_OBJECT', 'TRIM', 'JSON_EXTRACT', 'REPLACE']
        );


        $finalBlackList = array_merge($wordBlacklist, $functionBlacklist);
        foreach ($finalBlackList as $regex) {
            if (preg_match("#{$regex}#i", trim($value))) {//O regx não irá pegar espaços em branco, então o trim pode ser util.
                return true;
            }
        }


        return false;
    }

    /**
     * Select builder: $this->select('*')->where(blabla).
     * @param string $campos
     * @param bool $from
     * @param bool $alias
     * @return $this
     */
    public function select($campos = '*', $from = false, $alias = false)
    {
        if (!$from) {
            $from = $this->_table;
        }

        if (is_array($campos)) {
            $campos = implode(', ', $campos);
        }

        $alias = ($alias) ? " AS " . $alias : "";


        $this->_sql = "SELECT " . $campos . " FROM " . $from . " " . $alias;

        return $this;
    }

    /**
     * @return mixed
     */
    public function Fetch()
    {
        $result = $this->_stmt->fetch();
        $this->_stmt->closeCursor();
        $this->_stmt = null;
        return $result;
    }

    /**
     * Função que monta
     * @param type $dados
     * @param type $auditoria
     * @param type $tabela
     * @return type
     */
    public function insertOnDuplicateUpdate(&$dados, $auditoria = true, $tabela = false)
    {
        if (!$tabela) {
            $tabela = $this->_table;
        }

        //Inclui dados dados padrões de auditoria
        if ($auditoria) {
            $dataatual = date("Y-m-d H:i:s");
            $dados->__dateinsert = $dataatual;
            $dados->__dateativ = $dataatual;
            $dados->__userinsert = Util::GetUsuarioLogado();
            $dados->__userativ = Util::GetUsuarioLogado();
        }

        $valores = array();
        $sql = $this->gemInsertString($tabela, $dados, $valores, true);

        foreach ($valores as $v) {
            $valores[] = $v;
        }

        $ret = $this->query($sql, $valores);

        /**
         * @vcpablo - 12/04/2017
         * Retorna sempre 1, tanto quando registro foi criado quanto quando for atualizado.
         * Diferentemente do insert padrão, que retorna o id do último registro inserido.
         *
         * Ao utilizar esta função, verificar que o id do registro não é retornado.
         */
        return $ret->lastInsertId() > 0;
    }

    /**
     * Retorna o último ID inserido
     * @return int
     */
    public function lastInsertId()
    {
        return (int)$this->getDB()->lastInsertId();
    }

    public function insertIgnore(&$dados, $auditoria = true, $tabela = false)
    {
        if (!$tabela) {
            $tabela = $this->_table;
        }

        //Equaliza o objeto
        if (isset($dados->dados)) {
            $dados = $dados->dados;
        }

        //Inclui dados dados padrões de auditoria
        if ($auditoria) {
            $dataatual = date("Y-m-d H:i:s");
            $usuario = Util::GetUsuarioLogado();
            $dados->__dateinsert = $dataatual;
            $dados->__dateativ = $dataatual;
            $dados->__userinsert = $usuario;
            $dados->__userativ = $usuario;
        }

        $valores = array();
        $sql = $this->gemInsertString($tabela, $dados, $valores, false, [], true);

        $ret = $this->query($sql, $valores);

        $lastInsertId = $ret->lastInsertId();

        if ($lastInsertId === "0") {
            $lastInsertSql = $this->select('LAST_INSERT_ID() as id')->getSql();
            $lastInsertId = $this->query($lastInsertSql)->Fetch()["id"];
        }

        return $lastInsertId;
    }

    /**
     * Alias para ->join($tabela, $on, "LEFT")
     * @param $tabela
     * @param $on
     * @return $this
     */
    public function joinLeft($tabela, $on)
    {
        $this->join($tabela, $on, "LEFT");
        return $this;
    }

    /**
     * Builder do join
     * @param string $tabela Ex: "Tabela AS T"
     * @param string $on EX: "T.id = B.id"
     * @param string $tipo Por padrão INNER
     */
    public function join($tabela, $on, $tipo = "INNER")
    {
        $this->_join[] = " " . $tipo . " JOIN " . $tabela . " ON " . $on;

        return $this;
    }

    /**
     * Alias para ->join($tabela, $on, "RIGHT")
     * @param $tabela
     * @param $on
     * @return $this
     */
    public function joinRight($tabela, $on)
    {
        $this->join($tabela, $on, "RIGHT");
        return $this;
    }


    /**
     * Builder do having
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        if (strlen($having) > 0) {
            $this->_having = " " . str_replace(["HAVING", "having"], "", $having) . " ";
        }

        return $this;
    }

    /**
     * Builder do GroupBy
     * @param $group
     * @return $this
     */
    public function groupby($group)
    {
        $this->_group = $group;
        return $this;
    }

    /**
     * Builder do OrderBy
     * @param $order
     * @return $this
     */
    public function orderby($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Builder do Limit
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $limit = trim(preg_replace('/^LIMIT/', '', trim($limit)));
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Realiza um count simples  na tabela
     * @param  $where string
     * @return mixed
     */
    public function count($where = false)
    {

        $sql = $this->select("COUNT({$this->_primary}) AS Total");

        if ($where) {
            $sql->where($where);
        }

        $dados = $this->query((string)$sql)->fetch();

        if ($dados) {
            return $dados;
        }
    }

    public function where($condicao)
    {
        $condicao = trim(preg_replace('/^WHERE/', '', trim($condicao)));
        $this->_where[] = " " . $condicao . " ";

        return $this;
    }

    /**
     * Atualiza os dados da tabela
     * @param $dados
     * @param $where
     * @return bool|AbstractSQL
     */
    public function update($dados, $where)
    {
        if (!$this->_table) {
            throw new RuntimeException("The model's table is not defined");
        }

        if ($where === 1 || $where === "1" || $where === true || strlen($where) == 0 || trim($where) == "") {
            throw new BadMethodCallException("A clausula where inválida: “{$where}”  em " . __CLASS__);
        }


        $sql = "UPDATE " . $this->_table . " SET ";


//Remove qualquer campo que não esteja na tabela ou que não tenha valor.

        $arraydados = $this->sanitize($dados);

        $campos = "";
        $valores = array();
        $total = count($arraydados);
        $count = 1;


        foreach ($arraydados as $key => $value) {
            if ($count < $total || $count > 0 && $count != $total) {
                $s = ",";
            } else {
                $s = "";
            }
            if ($this->formatValue($value)) {
                $campos .= "`" . $key . "` = $value" . $s;
            } else {
                $campos .= "`" . $key . "` = ?" . $s;
                $valores[] = $value;
            }
            $count++;
        }

        $sql .= $campos;
        $sql .= ' WHERE ' . $where . ';';


        $ret = $this->query($sql, $valores);

        if (!empty($ret)) {
            return true;
        }

        return $ret;
    }

    /**
     * Método que realiza o Delete de dados no sistema.
     * @param type $where
     * @return type
     * @throws BadMethodCallException
     */
    public function delete($where)
    {
        if (!$this->_table) {
            throw new RuntimeException("The model's table is not defined");
        }
        if (!isset($this->_primary) && !is_null($this->_primary) && $this->_primary !== "") {
            throw new BadMethodCallException("Chave Primária não definida para " . __CLASS__);
        }
        $where = trim($where);

        //Se não for um string ou for um número.
        if (!is_string($where) || is_numeric($where)) {
            if (is_array($where)) {
                $id = implode(",", $where);
                $where = $this->_primary . " IN ($id)";
            } else if (is_numeric($where)) {
                $where = $this->_primary . " = $where";
            } else {
                $type = gettype($where);
                throw new BadMethodCallException("Condição de exclusão não permitida($type) para  " . __CLASS__);
            }
        } else {
            /**
             * Valida se é um where válido
             * ex:
             * id = 'abc123' -> válido
             * where id = 1 -> inválido
             * 1 -> inválido (se nao cair no if de cima)
             * NaN -> inválido
             * NULL -> inválido
             */
            if (!preg_match('/( |=|>|<|\()/', $where) || preg_match('/(WHERE)/i', $where)) {
                throw new BadMethodCallException("A clausula where inválida: “{$where}”  em " . __CLASS__);
            }
        }


        $sql = "DELETE FROM ";
        $sql .= $this->_table;
        $sql .= " WHERE " . $where . ";";


        return $this->getDB()->exec($sql);
    }

    public function formatByType($value, $type = false)
    {
        if (!$type) {
            $type = gettype($value);
        }

        switch ($type) {
            case 'boolean':
                return ($value) ? 1 : 0;
            case 'integer':
                return $value;
            case 'float':
            case 'double':
                return $value; //TODO-> Formatar a vírcula para ponto
            case 'string':
                return "'" . addslashes($value) . "'";
            case 'NULL':
                return 'NULL';
            case 'array':
            case 'object':
                return "'" . json_encode($value) . "'";
            default:
                return $value;
        }
    }

    public function enumValues($enumfield, $table = false)
    {
        $table = ($table) ? $table : $this->_table;

        $sql = "SHOW COLUMNS FROM {$table} WHERE Field = '{$enumfield}'";
        $type = $this->query($sql)->Fetch();
        preg_match("/^enum\(\'(.*)\'\)$/", $type['Type'], $matches);
        $enum = explode("','", $matches[1]);
        return $enum;
    }

    /**
     * Método que executa uma procedure e trata os múltiplos rowsets
     *
     * @param type $procedure Instrução procedure sem o "CALL"
     */
    public function call($procedure)
    {
        if (!preg_match('/\(/', $procedure) || preg_match('/(CALL)/i', $procedure)) {
            throw new BadMethodCallException("Parâmetro de procedure inválido: “{$procedure}”  em " . __CLASS__);
        }
        $stmt = $this->getDB()->prepare("CALL $procedure");
        $stmt->execute();
        $data = [];
        do {
            $data[] = $stmt->fetchAll();
        } while ($stmt->nextRowset() && $stmt->columnCount());
        return $data;
    }

    public function getTableName()
    {
        return $this->_table;
    }

    public function __destruct()
    {
        //        try {
        //            try {
        //                if ($this->_stmt) {
        //                    $this->_stmt->closeCursor();
        //                    //Error while sending STMT_CLOSE packet. PID=X
        //                    $this->_stmt = null;
        //                }
        //            } catch (\Exception $e) {
        //                //noop
        //            }
        //            $this->_db = null;
        //        } catch (\Exception $e) {
        //            //Silent exception
        //            new SysException("Unable do destruct connection: " . $e->getMessage(), $e->getCode(), $e, SysException::WARNING);
        //        }
    }

    public function __toString()
    {
        return $this->getSql();
    }
}