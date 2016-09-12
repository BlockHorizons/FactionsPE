<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use evalcore\command\parameter\type\primitive\TypeString;
use factions\entity\FPlayer;
use factions\event\faction\FactionPowerChangeEvent;
use factions\utils\Text;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class SetPowerSubCommand extends Command
{
	
	public function __construct(FactionsPE $plugin)
	{
	    parent::__construct($plugin, "setpower", "Set player's power level", FactionsPE::SETPOWER, ["sp"]);

		$this->addParameter(new Parameter("player", new TypeString(), false, true));
		$this->addParameter(new Parameter("power", new TypeInteger(), false, true));
	}

	public function execute(CommandSender $sender, $label, array $args) : bool
	{
	    if(!parent::execute($sender, $label, $args)) return true;

		// Args
		$fsender = FPlayer::get($sender);
		$playerName = $this->getParameter("player")->getValue();
		if(!($p = $this->getPlugin()->getServer()->getPlayer($playerName))) {
			$fsender->sendMessage(Text::parse("<red>Player '%var0' couldn't be found", $playerName));
			return false;
		}
		$player = FPlayer::get($p);
		$power = $this->getParameter("power")->getValue();
		
		// Power
		$oldPower = $player->getPower();
		$newPower = $player->getLimitedPower($power);
		
		// Detect "no change"
		$difference = abs($newPower - $oldPower);
		$maxDifference = 0.1;
		if ($difference < $maxDifference)
		{
			$sender->sendMessage(Text::parse("%var0's <i>power is already <h>%var1<i>.", $player->getDisplayName(), $newPower));
			return false;
		}

		// Event
		$event = new FactionPowerChangeEvent($fsender, $player, FactionPowerChangeEvent::COMMAND, $newPower);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) return;
		
		// Inform
		$sender->sendMessage(Text::parse("<i>You changed %var0's <i>power from <h>%var1 <i>to <h>%var2<i>.", $player->getDisplayName(),  $oldPower, $newPower));
		if ($fsender !== $player)
		{
			$player->sendMessage(Text::parse("%var0 <i>changed your power from <h>%var1 <i>to <h>%var2<i>.", $fsender->getDisplayName(), $oldPower, $newPower));
		}
		
		// Apply
		$player->setPower($newPower);
		return true;
	}
	
}