<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasRank;
use factions\objs\Rel;
use pocketmine\Player;
use factions\entity\FPlayer;
use factions\utils\Text;
use factions\entity\Flag;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class OpenFactionSubCommand extends Command {
	
	public function __construct(FactionsPE $plugin) {
		parent::__construct($plugin, "open", "Let anyone join", FactionsPE::OPEN, [], []);
		$this->addRequirement(new ReqBePlayer());
		$this->addRequirement(new ReqHasFaction());
		$this->addRequirement(new ReqHasRank(Rel::LEADER));
	}

	public function execute(CommandSender $sender, $label, array $args) : BOOL {
		if(!parent::execute($sender, $label, $args)) return true;
		/** @var Player $sender */
		$fplayer = FPlayer::get($sender);
		$faction = $fplayer->getFaction();

		if($faction->getFlag(Flag::OPEN)){
			$sender->sendMessage(Text::parse('command.open.already.opened'));
			return true;
		}
		$faction->setFlag(Flag::OPEN, true);
		$sender->sendMessage(Text::parse('command.open.success'));
		return true;
	}

}