<?php
namespace factions\event\player;

use factions\base\EventBase;
use pocketmine\event\Cancellable;
use factions\interfaces\IFPlayer;

class PlayerPowerChangeEvent extends EventBase implements Cancellable {
	
	const TIME = 0x00;
	const DEATH = 0x01;

	public static $handlerList = NULL;

	public function __construct(IFPlayer $player, $newPower, $reason = self::TIME) {
		$this->player = $player;
		$this->newPower = $newPower;
		$this->reason = $reason;
	}

	public function getReason() : int {
		return $this->reason;
	}

	public function getPlayer() : IFPlayer {
		return $this->player;
	}

	public function getNewPower() : int {
		return $this->newPower;
	}

	public function setPower(int $power) {
		$this->newPower = $power;
	}

}