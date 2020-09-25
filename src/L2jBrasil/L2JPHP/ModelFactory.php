<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP;


use Application\DB\Controllers\AbstractModel;
use L2jBrasil\L2JPHP\Utils\FileSystem;

class ModelFactory
{

    /**
     * @var ConfigSet
     */
    private static $_configset;

    public function __construct(ConfigSet $configset = null)
    {
        if(!$configset){
            $configset = ConfigSet::getDefaultInstance();
        }

        //TODO : Create more elgant way to define db Driver per model
        if($configset->_dist == "L2OFF" && $configset->_dbDriver != "dblib"){
            throw new \Exception("L2OFF require mssql compatible driver, such as dblib");
        }

        self::$_configset = $configset;
    }

    /**
     * @param $modelName
     * @return AbstractModel
     */
    public static function build($modelName)
    {

        $ClassName = self::getClassName($modelName);

        if (!class_exists($ClassName)) {
            $TryStdClass = self::getClassName($modelName);
            if (class_exists($TryStdClass)) {
                $ClassInstance = new $TryStdClass(self::$_configset);
            } else {
                throw  new \RuntimeException("The model '$modelName'  not exists");
            }
        } else {
            $ClassInstance = new $ClassName(self::$_configset);
        }


        return $ClassInstance;
    }

    private static function getClassName($modelName, $l2version = null, $distribuition = null)
    {
        if (!$l2version) {
            $l2version = self::getL2Version();
        }

        if (!$distribuition) {
            $distribuition = self::getDist();
        }


        return '\L2jBrasil\L2JPHP\Models\Dist\\' . $l2version . '\\' . $distribuition . '\\' . str_replace('/', '\\', $modelName);

        //$CharactersModel = \L2jBrasil\L2JPHP\Models\ModelFactory::build('Players/Characters');
        //return \L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Characters();
    }

    private static function getL2Version()
    {
        if (!self::$_configset->_version) {
            throw  new \RuntimeException("The Distribuition is not defined. Please define L2JBR_L2VERSION constant. Eg. Interlude ");
        }

        //Valida se a pasta Models\Dist\Interlude existe
        $versionDir = FileSystem::normalizePath(FileSystem::mountDir(
            array(dirname(__FILE__),"Models",
                "Dist", self::$_configset->_version)));


        if (!is_dir($versionDir)) {
            throw  new \RuntimeException("The dist " . self::$_configset->_dist . " is not implemented yet. please check {$versionDir} To know how to ask for a new implementation go to {URL_TO_PULL_REQUEST_ETC} ");
        }
        return self::$_configset->_version;
    }

    private static function getDist()
    {
        if (!self::$_configset->_dist) {
            throw  new \RuntimeException("The L2 Version is not defined. Please define L2JBR_DIST constant. Eg. L2JSERVER ");
        }

        //Valida se a pasta Models\Dist\Interlude\L2JSERVER existe
        $distDir = FileSystem::normalizePath(FileSystem::mountDir(
            array(dirname(__FILE__),"Models",
                "Dist", self::getL2Version(), self::$_configset->_dist)));


        if (!is_dir($distDir)) {
            throw  new \RuntimeException("The dist " . self::$_configset->_dist . " is not implemented yet. please check {$distDir} To know how to ask for a new implementation go to {URL_TO_PULL_REQUEST_ETC} ");
        }

        return self::$_configset->_dist;
    }

    private static function getConfigset(){
        ConfigSet::getInstance();
    }


}