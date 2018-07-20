# L2JPHP

A PHP Framework for harmonized L2J application development.

## "One library to rule them all"


![L2JBrDevelopers](http://i.imgur.com/bhBwp7U.jpg "Desenvolvido por Macacos altamente treinados")





```php
<?php

//$CharactersModel = new \L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Characters();
$CharactersModel = \L2jBrasil\L2JPHP\ModelFactory::build('Players/Characters');
$CharactersModel->get('ID');
$CharactersModel->update('ID', ["name"=> "Grundor"]);
$CharactersModel->ban('ID');
$CharactersModel->all(['name','level'],false,'level DESC ',10); //Top10 Level


```



