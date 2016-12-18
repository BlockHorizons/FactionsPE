<?php
use factions\entity\Faction;
use factions\manager\Factions;

$faction = Factions::getByName("test");
if(!$faction) {
	$faction = new Faction("dummy-id", ["name" => "TEST"]);
	$faction->save();
}

$this->getLogger()->info("Performing tests on DUMMY faction");
var_dump([
	"offline" => $faction->isConsideredOffline(),
	"online" => $faction->isConsideredOnline(),
	"normal" => $faction->isNormal(),
	"none" => $faction->isNone(),
	]);
var_dump(Faction::createId());
var_dump(Faction::createId());
var_dump(Faction::createId());
var_dump(Faction::createId());
var_dump(Faction::createId());