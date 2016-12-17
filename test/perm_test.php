<?php
use factions\permission\Permission;
use factions\relation\Relation;
use factions\manager\Factions;

$faction = Factions::getByName("test");

if($faction->getPermitted(Permission::BUILD) === Permission::getPermBuild()->getStandard()) {
	$this->getLogger()->info("Test successful!");
} else {
	$this->getLogger()->info("Test failed!");
}
