<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;

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


    public function ban($id)
    {
        return $this->update($id, [
            "access_level" => -1
        ]);
    }
}