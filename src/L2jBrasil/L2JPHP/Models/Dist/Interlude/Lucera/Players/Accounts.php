<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Dist\Interlude\Lucera\Players;



use L2jBrasil\L2JPHP\Models\AbstractSQL;
use L2jBrasil\L2JPHP\Models\Dist\Classic\Lucera\Players\Characters;
use L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Accounts as DefaultAccounts;

class Accounts extends DefaultAccounts implements \L2jBrasil\L2JPHP\Models\Interfaces\Players\Accounts
{

    protected $_table = 'accounts';
    protected $_primary = 'login';
    protected $_passencodemethod = "whirlpool";

    /**
     * @var array
     */
    protected $_tableMap = [
        "login" => "login",
        "password" => "password",
        "lastactive" => "lastactive",
        "access_level" => "accessLevel",
        "lastIP" => "lastIP",
        "lastServer" => "lastServer",
        "email" => "l2email",
    ];
    /*
    Columns:
    login varchar(45) PK
    password varchar(45)
    email varchar(255)
    created_time timestamp
    lastactive bigint(13) UN
    accessLevel tinyint(4)
    lastIP char(15)
    lastServer tinyint(4)
    */
    /**
     * @param $id
     * @return bool|AbstractSQL|mixed
     */
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
        $CharactersModel = new Characters(parent::getConfigSet());

        $accountCol = $CharactersModel->translate("account_name");
        $accountName = $CharactersModel->quote($login);

        return $CharactersModel->translateDataObj(
            $CharactersModel->select("
                        characters.* , 
                        S0.level as level,
                        S0.class_id as classid,
                        0 as nobless,
                        clanPledge.name as clan_name,
                        clanData.clan_level,
                        allyData.ally_name
			", $CharactersModel->getTableName(), "characters")
                ->join("clan_data as clanData", "characters.clanid = clanData.clan_id", "left") //TODO: Normalize
                ->join("clan_subpledges as clanPledge", "clanPledge.clan_id = clanData.clan_id and clanPledge.type = 0", "left") //TODO: Normalize
                ->join("ally_data as allyData", "allyData.ally_id = clanData.ally_id", "left") //TODO: Normalize
                ->join("character_subclasses AS S0", "S0.char_obj_id = characters.obj_Id AND S0.isBase = '1'", "left") //TODO: Normalize
                // ->join("character_subclasses AS S1", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '1'", "left") //TODO: Normalize
                //  ->join("character_subclasses AS S2", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '2'", "left") //TODO: Normalize
                //  ->join("character_subclasses AS S3", "S1.char_obj_id = characters.obj_Id AND S1.class_index = '3'", "left") //TODO: Normalize
                ->where("{$accountCol} = {$accountName}")
                ->query()
                ->FetchAll()
            , true);
    }
}