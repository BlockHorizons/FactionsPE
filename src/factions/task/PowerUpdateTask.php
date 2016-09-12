<?php
namespace factions\task;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

use factions\FactionsPE;
use factions\entity\FPlayer;
use factions\event\player\PlayerPowerChangeEvent;

class PowerUpdateTask extends PluginTask {
	
	protected $delayInTicks;

	public function __construct(FactionsPE $plugin, $delayInTicks) {
		parent::__construct($plugin);

		$this->delayInTicks = $delayInTicks;
	}

	public function onRun($currentTick) {

		$delay = $this->delayInTicks;
		foreach (FPlayer::getAllOnline() as $player)
		{
			if ($player->isNone()) continue;
			if ($player->isAlive() === false) continue;
			
			$newPower = $player->getPower() + ($player->getPowerPerHour() * ($delay / (20 * 60 * 60 * 1) ) );
			
			$event = new PlayerPowerChangeEvent($player, $newPower, PlayerPowerChangeEvent::TIME);
			$this->getOwner()->getServer()->getPluginManager()->callEvent($event);
			if ($event->isCancelled()) continue;
			$newPower = $event->getNewPower();
			
			$player->setPower($newPower);
		}
	}

}
