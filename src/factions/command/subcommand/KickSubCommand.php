<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasPerm;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\utils\RelationUtil;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;


class KickSubCommand extends Command
{

	public function __construct(FactionsPE $plugin)
	{
		parent::__construct($plugin, "kick", "Kick a player from your faction", FactionsPE::KICK);

		$this->addRequirement(new ReqBePlayer());
		//$this->addRequirement(self::REQ_SPECIFIC_ARGS, 1);
		$this->addRequirement(new ReqHasFaction());
		$this->addRequirement(new ReqHasPerm(Perm::getPermKick()));
	}

	public function execute(CommandSender $sender, $label, array $args) : BOOL
	{
		if (!parent::execute($sender, $label, $args) === false) return true;

		/** @var Player $sender */
		$fsender = FPlayer::get($sender);

		if (!($player = $this->getPlugin()->getServer()->getPlayer($args[0])) instanceof Player) {
			$player = FPlayer::getByName($args[0]);
		} else {
			$player = FPlayer::get($player);
		}
		if (!$player instanceof IFPlayer) {
			$sender->sendMessage(Text::parse('command.kick.player.not.found', $args[0]));
			return true;
		}

		if ($player->isLeader() and !$sender->hasPermission("factions.command.kick.leader")) {
			$sender->sendMessage(Text::parse('command.kick.no.perm.to.kick.leader', $player->getDisplayName()));
			return true;
		}

		if (RelationUtil::isLowerThan($player->getRole(), $fsender->getRole())) {
			$sender->sendMessage(Text::parse('command.kick.higher.rank', $player->getDisplayName()));
		}

		$player->leave();
		$player->sendMessage(Text::parse('command.kick.target.inform', $sender->getDisplayName())); # TODO: Reason

		return true;
	}

}