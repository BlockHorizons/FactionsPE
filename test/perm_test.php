<?php
use factions\permission\Permission;
use factions\relation\Relation;
use factions\manager\Factions;
use factions\manager\Permissions;

$faction = Factions::getByName("test");

if($faction->getPermitted(Permission::BUILD) === Permissions::getById(Permission::BUILD)->getStandard()) {
	$this->getLogger()->info("Test successful!");
} else {
	$this->getLogger()->info("Test failed!");
}
