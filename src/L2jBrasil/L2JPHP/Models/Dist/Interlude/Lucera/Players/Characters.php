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


    /**
     *
     */
    public function giveItem($ownderId, $itemId, $count = 1, $extra = [])
    {
        // INSERT INTO `l2jdb`.`items_delayed`
        // ( `owner_id`, `item_id`, `count`, `description`)
        // VALUES ('268477601',1, '57', '99', 'teste');
        /*
         Table: items_delayed
            Columns:
            payment_id int AI PK
            owner_id int
            item_id smallint UN
            count int UN
            enchant_level smallint UN
            attribute smallint
            attribute_level smallint
            flags int
            payment_status tinyint UN
            description varchar(255)
         * */
    }
}