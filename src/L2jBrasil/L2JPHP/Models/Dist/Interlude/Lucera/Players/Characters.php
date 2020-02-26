<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\Lucera\Players;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;

class Characters extends AbstractBaseModel implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Characters
{
    protected $_table = 'characters';
    protected $_primary = 'charId';
    protected $_tableMap = [
        "name" => "char_name",
        "id" => "obj_Id",
        "account_name" => "account_name",
        "sex" => "sex"
    ];

    public function ban($id)
    {
        // TODO: Implement ban() method.
    }
}