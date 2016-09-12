<?php
namespace factions;

use factions\command\parameter\type\TypeFaction;
use pocketmine\command\ConsoleCommandSender;

use evalcore\utils\DataCage;
use evalcore\utils\SmartData;

class Tester
{

    public static function test(FactionsPE $p) {
    	$dc = new DataCage("Test", new SmartData($p->getDataFolder()."gameplay_parsed.yml"));
    	$p->getLogger()->debug("Loaded data cage has ".count($dc->getAll())." keys in it");
    	$dc->setValue("test-key", "Hey, I was doing just fine before I met you", DataCage::FLAG_CREATE, ["description" =>
    		"I'm here just for fun :))", "default" => null]);
    	$dc->defaults();
    }

}