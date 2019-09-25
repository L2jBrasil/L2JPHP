<?php


namespace L2jBrasil\L2JPHP;


class ConfigSet extends \stdClass
{

    public  $_dist =  "L2JSERVER";
    public  $_version =  "Interlude";
    public  $_salt =  'change_it_for_something_else';
    public  $_dbDriver = "mysql";
    public  $_dbHost = "localhost";
    public  $_dbPort =  3306;
    public  $_dbName =  "l2jdb";
    public  $_dbUser =  "root";
    public  $_dbPwd = "";


    public final static function getDefaultInstance()
    {
        if(!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public static function hash(){
        $self = new self;
        return md5(serialize($self));
    }
}