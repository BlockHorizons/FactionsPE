<?php
use factions\entity\Faction;

$names = [
	"123Test",
	"Hello World!",
	"a",
	"...",
	"op",
	"mask",
	"COC"
];

foreach ($names as $key) {
	$errors = Faction::validateName($key);
	if(empty($errors)) {
		echo "$key: valid".PHP_EOL;
	} else {
		echo "$key: ".count($errors)." errors - ".PHP_EOL;
		foreach ($errors as $error) {
			echo "error: ".$error.PHP_EOL;
		}
	}
}