<?php


namespace L2jBrasil\L2JPHP;


class ConfigSet extends \stdClass
{

    public  $_dist = L2JBR_DIST ??  "L2JSERVER";
    public  $_version = L2JBR_L2VERSION ?? "Interlude";
    public  $_salt = L2JBR_SALT ?? 'change_it_for_something_else';
    public  $_dbDriver = L2JBR_DB_DRIVER ?? "mysql";
    public  $_dbHost = L2JBR_DB_HOST ?? "localhost";
    public  $_dbPort = L2JBR_DB_PORT ?? 3306;
    public  $_dbName = L2JBR_DB_NAME ?? "l2jdb";
    public  $_dbUser = L2JBR_DB_USER ?? "root";
    public  $_dbPwd = L2JBR_DB_PWD ?? "";


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