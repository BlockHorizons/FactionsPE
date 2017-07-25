<?php
use factions\entity\Faction;
use factions\manager\Factions;

$f = new Faction("dummy", [
		"name" => "Dummy",
		"members" => ["leader" => ["ChrisPrime"]],
	]);
Factions::attach($f);

$f->setHome($this->getServer()->getDefaultLevel()->getSafeSpawn());