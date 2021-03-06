<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Interfaces\Players;


interface Accounts
{
    public function all();

    public function get($id);

    public function delete($id);

    public function update($id, $data);

    public function ban($id);

    public function register($login, $pass, $data = []);

    public function getCharacters($login);

    public function encodepwd($pwd);

    public function login($login, $password);
}