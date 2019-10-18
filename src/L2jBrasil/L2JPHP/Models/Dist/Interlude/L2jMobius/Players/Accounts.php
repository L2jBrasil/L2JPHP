<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\L2jMobius\Players;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;
use L2jBrasil\L2JPHP\Models\AbstractSQL;

class Accounts extends AbstractBaseModel implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Accounts
{

    protected $_table = 'accounts';
    protected $_primary = 'login';

    /**
     * @var array
     */
    protected $_tableMap = [
        "login" => "login",
        "password" => "password",
        "lastactive" => "lastactive",
        "access_level" => "accessLevel",
        "lastIP" => "lastIP",
        "lastServer" => "lastServer",
        "email" => "email",
    ];
    /*
    Columns:
    login varchar(45) PK
    password varchar(45)
    email varchar(255)
    created_time timestamp
    lastactive bigint(13) UN
    accessLevel tinyint(4)
    lastIP char(15)
    lastServer tinyint(4)
    */
    /**
     * @param $id
     * @return bool|AbstractSQL|mixed
     */
    public function ban($id)
    {
        return $this->update($id, [
            "access_level" => -1
        ]);
    }

    /**
     *
     * @param $login
     * @param $password
     * @return mixed
     *
     *
     */
    public function login($login, $password)
    {
        $login = $this->quote($login);
        $pass1 = $this->quote($this->encodepwd($password));


        $loginCol = $this->translate("login");
        $passwordCol = $this->translate("password");

        $account = $this->select("*")
            ->where("{$loginCol} = {$login}  AND {$passwordCol} = {$pass1} ")
            ->query()
            ->Fetch();

        $success = $account != null;

        if ($success) {
            $this->update($login, [
                $this->translate("lastIP") => $_SERVER['REMOTE_ADDR']
            ]);
        }

        return $success;
    }

    public function register($login, $password, $data = [])
    {
        if (!$this->exists($this->translate("login"), $login)) {
            $dados = [
                $this->translate("login") => $login,
                $this->translate("password") => $this->encodepwd($password),
            ];

            //Manipulate extra fields, eg email
            if (count($data) > 0) {
                foreach ($data as $col => $value) {
                    $dados[$this->translate($col)] = $value;
                }
            }

            $this->insert($dados);

            return $this->get($login);
        }
        return false;
    }

}