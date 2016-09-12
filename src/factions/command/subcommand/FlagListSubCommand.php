<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/2/16
 * Time: 1:00 AM
 */

namespace factions\command\subcommand;


use evalcore\command\Command;
use factions\entity\Flag;
use factions\FactionsPE;
use factions\objs\Factions;
use pocketmine\command\CommandSender;

class FlagListSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "flaglist", "See all available flags", "factions.command.flaglist", ["flags"]);
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOL
    {
        if (parent::execute($sender, $label, $args) == false) {
            return true;
        }

        if(isset($args[0])) {
            if(!($faction = Factions::getByName($args[0]))) {
                $sender->sendMessage("Faction '$args[0]' was not found!");
                return true;
            }
            $output = [$faction->getName()." flags:"];
            foreach($faction->getFlags() as $flag => $value) {
                $output[] = $flag.": ".($value ? "true" : "false");
            }
            foreach($output as $line) {
                $sender->sendMessage($line);
            }
            return true;
        }

        $output = [];
        foreach (Flag::getAll() as $flag) {
            if( ! $flag->isVisible() || false ) continue; # Overriding TODO
            $output[] = $flag->getName().": ".($flag->isStandard() ? "true" : "false");
        }
        foreach ($output as $line) {
            $sender->sendMessage($line);
        }
        return true;
    }
}