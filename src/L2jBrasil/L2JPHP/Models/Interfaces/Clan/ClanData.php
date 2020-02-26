<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Interfaces\Clan;

interface ClanData
{
    public function all();

    public function get($id);

    public function delete($id);

    public function update($id, $data);

    public function getReputation($id);

    public function setReputation($clan_id, $reputation);

    public function putReputation($clan_id, $reputation);

}