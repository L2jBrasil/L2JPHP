<?php
/**
 * Copyright (C) 2018 L2JBrasil
 * @autor Leonan Carvalho
 * @license MIT
 */

namespace L2jBrasil\L2JPHP\Models\Interfaces\Residences;

interface Castles
{
    public function all();

    public function get($id);

    public function update($id, $data);

    public function status($castleId);

    public function siege();

    public function siegeParticipants($castleId);

}