<?php

use L2jBrasil\L2JPHP\Models\AbstractBaseModel;

use \L2jBrasil\L2JPHP\Models\Interfaces\Players\Accounts as AccountsInterface;

class Accounts extends AbstractBaseModel implements AccountsInterface
{
    protected $_table = 'user_auth'; // ou user_account user_info
    protected $_primary = 'account';

    /**
     * Map all standard fields to table field
     * @var array
     */
    protected $_tableMap = [
        "login" => "account"
    ];


    public function ban($id)
    {
        // TODO: Implement ban() method.
    }

    public function register($login, $password, $data = [])
    {
        if (!$this->exists($this->translate("login"), $login)) {
            $insertData = [
                $this->translate("login") => $login,
                $this->translate("password") => $this->encodepwd($password),
            ];

            //TODO define $insertData

            return $this->get($login); //Todo check if default get can handler common get
        }
        return false;
    }

    public function getCharacters($login)
    {
        // TODO: Implement getCharacters() method.
    }

    public function encodepwd($pwd)
    {
        $array_mul = array ( 0 => 213119, 1 => 213247, 2 => 213203, 3 => 213821 ); $array_add = array ( 0 => 2529077, 1 => 2529089, 2 => 2529589, 3 => 2529997 ); $dst = $key = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0 ); for ( $i = 0; $i < strlen ( $pwd ); $i++ ) { $dst [ $i ] = $key [ $i ] = ord ( substr ( $pwd, $i, 1 ) ); } for ( $i = 0; $i <= 3; $i++ ) { $val [ $i ] = fmod ( ( $key [ $i * 4 + 0 ] + $key [ $i * 4 + 1 ] * 0x100 + $key [ $i * 4 + 2 ] * 0x10000 + $key [ $i * 4 + 3 ] * 0x1000000 ) * $array_mul [ $i ] + $array_add [ $i ], 4294967296 ); } for ( $i = 0; $i <= 3; $i++ ) { $key [ $i * 4 + 0 ] = $val [ $i ] & 0xff; $key [ $i * 4 + 1 ] = $val [ $i ] / 0x100 & 0xff; $key [ $i * 4 + 2 ] = $val [ $i ] / 0x10000 & 0xff; $key [ $i * 4 + 3 ] = $val [ $i ] / 0x1000000 & 0xff; } $dst [ 0 ] = $dst [ 0 ] ^ $key [ 0 ]; for ( $i = 1; $i <= 15; $i++ ) { $dst [ $i ] = $dst [ $i ] ^ $dst [ $i - 1 ] ^ $key [ $i ]; } for ( $i = 0; $i <= 15; $i++ ) { if ( $dst [ $i ] == 0 ) { $dst [ $i ] = 0x66; } } $encrypted = "0x"; for ( $i = 0; $i <= 15; $i++ ) { if ( $dst [ $i ] < 16 ) { $encrypted .= "0"; } $encrypted .= dechex ( $dst [ $i ] ); }
        return $encrypted;
    }


    public function login($login, $password)
    {
        $login = $this->quote($login);
        $pass1 = $this->quote($this->encodepwd($password));


        $loginCol = $this->translate("login");
        $passwordCol = $this->translate("password");

        $account = $this->select("TOP 1")
            ->where("{$loginCol} = {$login}  AND {$passwordCol} = {$pass1} ")
            ->query()
            ->Fetch();

        $success = $account != null;


        return $success;
    }
}