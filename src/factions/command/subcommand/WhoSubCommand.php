<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use factions\entity\FPlayer;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class WhoSubCommand extends Command
{
    
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "who", "Get more detailed information about player", "factions.command.who", ["whois"]);
        //$this->addRequirement(self::REQ_SPECIFIC_ARGS, 1);
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (!parent::execute($sender, $label, $args)) return true;

        $player = FPlayer::getByName($args[0]);
        if($player === NULL) {
            $sender->sendMessage("Player '{$args[0]}' was not found!");
            return true;
        }

        $sender->sendMessage("Showing info on player ".$player->getName().":");
        $sender->sendMessage("Has faction: ".($player->hasFaction() ? "true" : "false"));
        if($player->hasFaction()){
            $sender->sendMessage("Faction: ".$player->getFaction()->getName());
            $sender->sendMessage("Faction ID: ".$player->getFactionId());
            $sender->sendMessage("Power: ".$player->getPower());
            $sender->sendMessage("Rank: ".$player->getRole());
        }
        $sender->sendMessage("Online: ".($player->isOnline() ? "true" : "false") );
        $sender->sendMessage("Power: ". $player->getPower());
        return true;
    }

}