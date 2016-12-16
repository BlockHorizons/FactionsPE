<?php
return;

$data = [
	"name" => "Dummy",
	"firstPlayed" => time(),
	"power" => 4,
	"role" => \factions\relation\Relation::LEADER,
	"title" => "BOSS",
];
$md = new \factions\data\MemberData($data);
$md->save();

// Let's get back that data
$nd = $this->getDataProvider()->loadMember("Dummy");
var_dump($nd);