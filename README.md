# L2JPHP
A PHP Framework for standard L2J application development.

## "One library to rule them all"



```php
<?php

$CharactersModel = \L2jBrasil\L2JPHP\Models\ModelFactory::build('Players/Characters');
$CharactersModel->get('ID');
$CharactersModel->update('ID', (object)["name"=> "Grundor"]);
$CharactersModel->ban('ID');


```


# This repository is an work in progress (WIP) use with caution
