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


    protected $_tableMap = [
        "login" => "login",
        "password" => "password",
        "lastactive" => "lastactive",
        "access_level" => "access_level",
        "lastIP" => "lastIP",
        "lastServer" => "lastServer"
    ];


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
        $pass1 = base64_encode(pack('H*', sha1(trim($password))));
        $pass2 = base64_encode(hash('whirlpool', trim($password), true));


        $loginCol = $this->translate("login");
        $passwordCol = $this->translate("password");
        $account = $this->select()
            ->where("{$loginCol} == '{$login}'  AND ({$passwordCol} = '{$pass1}' OR {$passwordCol} = '{$pass2}')")
            ->query()
            ->Fetch();

        return $account;
    }

}