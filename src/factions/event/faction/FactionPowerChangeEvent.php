<?php
namespace factions\event\faction;

use factions\base\EventBase;
use factions\interfaces\IFPlayer;
use pocketmine\event\Cancellable;

class FactionPowerChangeEvent extends EventBase implements Cancellable {

	const CUSTOM = 0x00;
	const COMMAND = 0x01;
	
	public static $handlerList = null;

	/**
	 * @var IFPlayer $issuer
	 */
	protected $issuer;

	/**
	 * @var IFPlayer $player
	 */
	protected $player;

	/**
	 * @var int $reason
	 */
	protected $reason;


	public function __construct(IFPlayer $issuer, IFPlayer $player, $reason = FactionPowerChangeEvent::CUSTOM) {
		$this->issuer = $issuer;
		$this->player = $player;
		$this->reason = $reason;
	}

	public function getIssuer() : IFPlayer {
		return $this->issuer;
	}

	public function getPlayer() : IFPlayer {
		return $this->player;
	}

	public function getReason() {
		return $this->reason;
	}

}