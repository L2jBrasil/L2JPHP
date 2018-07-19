<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP;


use L2jBrasil\L2JPHP\Utils\FileSystem;

class ModelFactory
{

    /**
     * @param $modelName
     * @return AbstractBaseModel
     */
    public static function build($modelName)
    {

        $ClassName = self::getClassName($modelName);

        if (!class_exists($ClassName)) {
            $TryStdClass = self::getClassName($modelName, "Generic", "Generic");
            if (class_exists($TryStdClass)) {
                $ClassInstance = new $TryStdClass();
            } else {
                throw  new \RuntimeException("The model '$modelName'  not exists");
            }
        } else {
            $ClassInstance = new $ClassName();
        }


        return new $ClassInstance();
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
        if (!defined('L2JBR_L2Version')) {
            throw  new \RuntimeException("The Distribuition is not defined. Please define L2JBR_DIST constant. Eg. Interlude ");
        }

        //Valida se a pasta Models\Dist\Interlude existe
        $versionDir = FileSystem::normalizePath(FileSystem::mountDir(
            array(dirname(__FILE__),
                "Dist", L2JBR_L2Version)));


        if (!is_dir($versionDir)) {
            throw  new \RuntimeException("The L2 Version " . L2JBR_L2Version . " is not implemented yet. To know how to ask for a new implementation go to {URL_TO_PULL_REQUEST_ETC} ");
        }
        return L2JBR_L2Version;
    }

    private static function getDist()
    {
        if (!defined('L2JBR_L2Version')) {
            throw  new \RuntimeException("The L2 Version is not defined. Please define L2JBR_L2Version constant. Eg. L2JSERVER ");
        }

        //Valida se a pasta Models\Dist\Interlude\L2JSERVER existe
        $distDir = FileSystem::normalizePath(FileSystem::mountDir(
            array(dirname(__FILE__),
                "Dist", self::getL2Version(), L2JBR_DIST)));


        if (!is_dir($distDir)) {
            throw  new \RuntimeException("The dist " . L2JBR_DIST . " is not implemented yet. To know how to ask for a new implementation go to {URL_TO_PULL_REQUEST_ETC} ");
        }

        return L2JBR_DIST;
    }

}