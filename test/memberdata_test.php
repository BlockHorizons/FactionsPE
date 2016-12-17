<?php
use factions\entity\OfflineMember;

$member = new OfflineMember("dummy");
var_dump([
	"isRecruit" => $member->isRecruit(),
	"power" => $member->getPower(),
	"hasFaction" => $member->hasFaction(),
	"faction" => $member->getFaction(),
	]);