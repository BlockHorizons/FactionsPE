<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasRank;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\objs\Rel;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class SetNameSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "name", "Change name of your faction", "factions.command.setname");

        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
        $this->addRequirement(new ReqHasRank(Rel::LEADER));
        //$this->addRequirement(self::REQ_AT_LEAST_ARGS);
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOL
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        /** @var Player $sender */
        $name = $args[0];
        if (Factions::getByName($name) !== null) {
            $sender->sendMessage(Text::parse('command.name.taken', $name));
            return true;
        }
        if (!(ctype_alnum($args[0]))) {
            $sender->sendMessage(Text::parse("command.faction.name.wrong.format"));
            return true;
        }
        if (Text::isNameBanned($args[0])) {
            $sender->sendMessage(Text::parse("command.faction.name.banned"));
            return true;
        }
        if (Factions::getByName($args[0])) {
            $sender->sendMessage(Text::parse("command.create.faction.exists"));
            return true;
        }
        if (strlen($args[0]) > Settings::get('faction.name.length', 8)) {
            $sender->sendMessage("This name is too long. Please try again!");
            return true;
        }
        $fsender = FPlayer::get($sender);
        $fsender->getFaction()->setName($name);
        $fsender->getFaction()->sendMessage(Text::parse('command.name.set', $sender->getDisplayName(), $name));
        return true;
    }

}