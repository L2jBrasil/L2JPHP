<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;

class Characters extends AbstractBaseModel implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Characters
{
    protected $_table = 'characters';
    protected $_primary = 'charId';
    protected $_tableMap = [
        "name" => "charName",
        "id" => "charId"
    ];

    public function ban($id)
    {
        // TODO: Implement ban() method.
    }

    public function getOnline()
    {
        $onlineCol = $this->translate('online');
        $where = "{$onlineCol}  = 1";
        return $this->count($where);
    }
}