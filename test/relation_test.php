<?php
use factions\relation\Relation;
use factions\manager\Factions;
use factions\manager\Members;

$faction = Factions::getByName("test");
$member = Members::getByName("dummy");
$member->setFaction($faction);

var_dump($faction->getMembers());

var_dump($faction->getRelationTo($member));
$faction->setRelationWish($member->getFaction(), Relation::ENEMY);
var_dump($faction->getRelationWish($member->getFaction()));
var_dump($faction->getRelationTo($member));