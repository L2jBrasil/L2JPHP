<?php


namespace L2jBrasil\L2JPHP;


class ConfigSet extends \stdClass
{

    public  $_dist = L2JBR_DIST;
    public  $_version = L2JBR_L2VERSION;
    public  $_salt = L2JBR_SALT;
    public  $_dbDriver = L2JBR_DB_DRIVER;
    public  $_dbHost = L2JBR_DB_HOST;
    public  $_dbPort = L2JBR_DB_PORT;
    public  $_dbName = L2JBR_DB_NAME;
    public  $_dbUser = L2JBR_DB_USER;
    public  $_dbPwd = L2JBR_DB_PWD;


    public final static function getDefaultInstance()
    {
        if(!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public static function hash(){
        return md5(serialize(self));
    }
}