<?php
use factions\manager\Factions;
use factions\flag\Flag;

// Let's use Flag::INFINITY_POWER for testing
$faction = Factions::getByName('test');

$this->getLogger()->info("Testing Flag::INFINITY_POWER");
$faction->setFlag(Flag::INFINITY_POWER, true);
if($faction->getFlag(Flag::INFINITY_POWER)) {
	$this->getLogger()->info("Test successful!");
} else {
	$this->getLogger()->info("Flag test failed!");
}
