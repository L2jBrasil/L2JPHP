<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\Lucera\Players;


use L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Characters as DefaultCharacters;

class Characters extends DefaultCharacters implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Characters
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

    public function getOnline($ttl = 0)
    {
        // TODO: Implement getOnline() method.
    }
}