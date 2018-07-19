# L2JPHP
A PHP Framework for standard L2J application development.

## "One library to rule them all"



```php
<?php

$CharactersModel = \L2jBrasil\L2JPHP\Models\ModelFactory::build('Players/Characters');
$CharactersModel->get('ID');
$CharactersModel->update('ID', ["name"=> "Grundor"]);
$CharactersModel->ban('ID');
$CharactersModel->all(['name','level'],false,'level DESC ',10); //Top10 Level


```