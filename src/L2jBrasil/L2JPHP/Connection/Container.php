<?php


namespace L2jBrasil\L2JPHP\Connection;


class Container extends \stdClass
{
    use Singleton;

    public static $_intances = [];


    public static function save($hash, $obj){
        self::$_intances[$hash] = $obj;
    }
    public static function get($hash){
        return self::$_intances[$hash] ?? null;
    }
}