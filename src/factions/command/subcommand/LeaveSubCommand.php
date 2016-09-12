<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class LeaveSubCommand extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "leave", "Leave your current faction", FactionsPE::LEAVE, ["quit"], []);
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        FPlayer::get($sender)->leave();
        return true;
    }

}