<?php
/**
 * Created by PhpStorm.
 * User: Leonan
 * Date: 19/07/2018
 * Time: 12:47
 */

namespace L2jBrasil\L2JPHP\Models\Interfaces\Players;


interface Characters
{

    public function all();

    public function get($id);

    public function delete($id);

    public function update($id, $data);

    public function ban($id);

}