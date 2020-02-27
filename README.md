# L2JPHP

A PHP Framework for harmonized L2J application development.

## "One library to rule them all"


![L2JBrDevelopers](http://i.imgur.com/bhBwp7U.jpg "Desenvolvido por Macacos altamente treinados")


## Official Topic: https://www.l2jbrasil.com/index.php?/topic/126388-l2jphp-one-library-to-rule-them-all/



## Usage: 

```php
<?php

define('L2JBR_DIST', "L2JSERVER"); //What is the distribution?
define('L2JBR_L2VERSION', "Interlude"); //What is the chronicle? Kamael, God, Classic, any generec name.

//$CharactersModel = new \L2jBrasil\L2JPHP\Models\Dist\Interlude\L2JSERVER\Players\Characters(); //Compatible only with L2JSERVER databases
$CharactersModel = \L2jBrasil\L2JPHP\ModelFactory::build('Players/Characters'); //Compatible for all suported modules
$CharactersModel->get('ID');
$CharactersModel->update('ID', ["name"=> "Grundor"]);
$CharactersModel->ban('ID');
$CharactersModel->all(['name','level'],false,10,'level'); //Retorna os 10 personagens com maior level.

//Advanced:
$CharactersModel->select(['character.id','account.name'])
    ->join(\L2jBrasil\L2JPHP\ModelFactory::build('Players/Account'))
    ->orderby('level')
    ->limit(100)
    ->query()
    ->FetchAll();


```
# Modules

* Players
  * Characters
  * Account
  * Iventory
  * Wharehouse
* Clan
  * ClanData
  * Ally
* NPC
  * RaidBoss
  * GrandBoss
  * NPC
  * DropList
# Coverage

| Rev/Dist      | Interlude | H5  |Gracia|Classic|
| ------------- |:---------:|:---:|:----:|:-----:|
| L2jServer      |10%|  | | |
| L2jMobius      |10%| | | |
| aCis | | | | |
| Lucera |10%| | | |

