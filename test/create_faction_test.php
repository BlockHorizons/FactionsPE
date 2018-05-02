<?php

use factions\manager\Factions;
use factions\manager\Members;
use factions\manager\Flags;
use factions\manager\Permissions;
use factions\flag\Flag;
use factions\permission\Permission;
use factions\entity\Faction;

$faction = Factions::create(
	Faction::createId(),
	"Test",
	"Test Description", 
	Factions::createMembersList(
		Members::get("Dummy", true),
		["Hyena", "Hippo"],
		[],
		["Kugo"]
	),
	[
		Flags::getById(Flag::PERMANENT),
		"pvp" => false,
		Flag::OPEN => true
	],
	[
		Permissions::getById(Permission::STATUS),
		"build" => ["recruit", "leader"],
		Permission::PAINBUILD => ["officer", "member"]
	],
	$data = []
);

var_dump($faction);
Factions::attach($faction);