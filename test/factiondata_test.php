<?php
return;
// Testing FactionData class
use factions\data\FactionData;

$source = [
	"name" => "TEST",
	"id" => "DUMMY-ID"
];
$fd = new FactionData($source);
// We cant call $fd->save(); because that will throw an error
$this->getDataProvider()->saveFaction($fd);

// Load
$nd = $this->getDataProvider()->loadFaction($source["id"]);
var_dump($nd);