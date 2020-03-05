<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players;


use L2jBrasil\L2JPHP\Models\AbstractBaseModel;
use L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Characters as DefaultCharacters;

class Accounts extends AbstractBaseModel implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Accounts
{

    protected $_table = 'accounts';
    protected $_primary = 'login';


    protected $_tableMap = [
        "login" => "login",
        "password" => "password",
        "lastactive" => "lastactive",
        "access_level" => "access_level",
        "lastIP" => "lastIP",
        "lastServer" => "lastServer"
    ];


    public function ban($id)
    {
        return $this->update($id, [
            "access_level" => -1
        ]);
    }

    /**
     *
     * @param $login
     * @param $password
     * @return mixed
     *
     *
     */
    public function login($login, $password)
    {
        $login = $this->quote($login);
        $pass1 = $this->quote($this->encodepwd($password));


        $loginCol = $this->translate("login");
        $passwordCol = $this->translate("password");

        $account = $this->select("*")
            ->where("{$loginCol} = {$login}  AND {$passwordCol} = {$pass1} ")
            ->query()
            ->Fetch();

        $success = $account != null;

        if ($success) {
            $this->update($login, [
                $this->translate("lastIP") => $_SERVER['REMOTE_ADDR']
            ]);
        }

        return $success;
    }

    public function register($login, $password, $data = [])
    {
        if (!$this->exists($this->translate("login"), $login)) {
            $insertData = [
                $this->translate("login") => $login,
                $this->translate("password") => $this->encodepwd($password),
            ];

            //Manipulate extra fields, eg email
            if (count($data) > 0) {
                foreach ($data as $col => $value) {
                    $insertData[$this->translate($col)] = $value;
                }
            }

            $this->insert($insertData);

            return $this->get($login);
        }
        return false;
    }

    public function getCharacters($login)
    {
        $CharactersModel = new DefaultCharacters(parent::getConfigSet());

        $accountCol = $CharactersModel->translate("account_name");
        $accountName = $CharactersModel->quote($login);

        $Characters = $CharactersModel->translateDataObj(
            $CharactersModel->select("
                        characters.* , 
                        clanData.clan_name, clanData.clan_level, clanData.ally_name, 
                        S1.class_id AS subclass1,
                        S2.class_id AS subclass2,
                        S3.class_id AS subclass3 
			", $CharactersModel->getTableName(), "characters")
                ->join("clan_data as clanData", "characters.clan_id = clanData.clan_id", "left") //TODO: Normalize
                ->join("character_subclasses AS S1", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '1'", "left") //TODO: Normalize
                ->join("character_subclasses AS S2", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '2'", "left") //TODO: Normalize
                ->join("character_subclasses AS S3", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '3'", "left") //TODO: Normalize
                ->where("{$accountCol} = {$accountName}")
                ->query()
                ->FetchAll()
            , true);
    }
}