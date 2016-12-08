<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\entity;

use factions\integrations\Economy;
use factions\interfaces\EconomyParticipator;
use factions\interfaces\IFPlayer;
use factions\interfaces\Named;
use factions\interfaces\RelationParticipator;
use factions\objs\Plots;
use factions\objs\Rel;
use factions\utils\RelationUtil;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\IPlayer;
use pocketmine\command\ConsoleCommandSender;

class FPlayer extends OfflineFPlayer implements EconomyParticipator, RelationParticipator, Named
{

	/** @var IFPlayer[] $storage */
	private static $storage = [];


	/** @var string $factionHereId */
	public $factionHereId = "";
	/** @var $lastActivityMillis */
	protected $lastActivityMillis;
	/** @var boolean $mapAutoUpdating */
	protected $mapAutoUpdating = null;
	/** @var Faction $autoClaimFaction */
	private $autoClaimFaction = null;
	/** @var bool $seeingChunk */
	private $seeingChunk = false;
	
	public function __construct(Player $player)
	{
		// I'm not sure what these checks below do. :confused:
		$this->name = $player->getName();
		FPlayer::attach($this);
		parent::__construct($player instanceof Player ? $player->getName() : $player);
		if($player instanceof Player) {
			$this->player = $player;
		}
	}

	// -------------------------------------------- //
	// STORAGE FUNCTIONS
	// -------------------------------------------- //

	public static function attach(IFPlayer $player)
	{
		if (!self::isRegistered($player)) self::$storage[$player->getName()] = $player;
	}

	public static function isRegistered(IFPlayer $player) : BOOL // TODO: Rename -> contains
	{
		return isset(self::$storage[$player->getName()]);
	}

	/**
	 * You should only pass player names when manipulating with offline players
	 *
	 * @param $player
	 * @return IFPlayer|FPlayer|OfflineFPlayer
	 */
	public static function get($player) : IFPlayer
	{
		if($player instanceof ConsoleCommandSender) {
			return self::get("Console");
		}
		if($player instanceof IFPlayer) {
			if($player instanceof OfflineFPlayer) {
				$player = $player->getName();
			} else {
				$player = $player->getPlayer();
			}
		}
		if($player instanceof IPlayer) {
			// Handling player object
			if (($ret = self::getByName($player->getName())) !== NULL) {
				// if stored instance is offline - detach from storage
				if($ret instanceof FPlayer === false) {
					self::detach($ret);
				}
			}
			return self::create($player);
		} else {
			// Handling name aka offline player
			if (($ret = self::getByName($player)) !== NULL) {
				if($ret->isOnline()) {
					if(!$ret->isNone()) self::detach($ret); // player was stored in offline instance altough he's online
					else return $ret;
				}
			}
			return self::createOffline($player);
		}
	}

	/**
	 * This function won't create new IFPlayer instance it will just check
	 * is there offline/online player stored in self::$storage
	 * with the same name
	 *
	 * @return IFPlayer|NULL
	 * @param $name
	 */
	public static function getByName($name) {
		foreach(self::$storage as $player){
			if(strtolower($player->getName()) === strtolower($name)) return $player;
		}
		return NULL;
	}

	/**
	 * Remove a member from storage
	 * @param IFPlayer $player
	 */
	public static function detach(IFPlayer $player)
	{
		if (self::isRegistered($player)) {
			unset(self::$storage[$player->getName()]);
			if ($player instanceof FPlayer and $player->hasFaction()) {
				self::get($player->getName());
			}
		}
	}

	/**
	 * Create a FPlayer instance for Player if he doesn't have one already
	 * @param Player $player
	 */
	public static function create(Player $player)
	{
		if (($ret = self::getByName($player->getName())) !== NULL) return $ret;
		return new FPlayer($player);
	}

	/**
	 * Returns a OfflinePlayer instance for player with $name
	 * @param string $name
	 */
	public static function createOffline(string $name) : OfflineFPlayer
	{
		if (($ret = self::getByName($name)) !== NULL) return $ret;
		return new OfflineFPlayer($name);
	}

	/**
	 * @return IFPlayer[]
	 */
	public static function getAllOnline() : ARRAY
	{
		$ret = [];
		foreach (self::$storage as $fplayer) {
			if ($fplayer->isOnline()) $ret[] = $fplayer;
		}
		return $ret;
	}

	/**
	 * Makes sure that every member data is saved
	 */
	public static function saveAll() {
		foreach(self::getAll() as $player) {
			$player->save();
		}
	}

	/**
	 * @return IFPlayer[]
	 */
	public static function getAll()
	{
		return self::$storage;
	}
	

	/*
	 * ----------------------------------------------------------
	 * METHODS
	 * ----------------------------------------------------------
	 * 
	 * The real functions 
	 *
	 */

	public function getAutoClaimFaction() : STRING // TODO: Rewrite the autoClaimFaction thingy
	{
		return $this->autoClaimFaction;
	}

	public function setAutoClaimFaction(Faction $autoClaimFaction)
	{
		$this->autoClaimFaction = $autoClaimFaction;
	}

	public function isAutoClaiming() : BOOL
	{
		return $this->autoClaimFaction instanceof Faction;
	}

	public function isSeeingChunk() : BOOL
	{
		return $this->seeingChunk;
	}

	public function setSeeingChunk(bool $seeingChunk)
	{
		$this->seeingChunk = $seeingChunk;
	}

	public function resetFactionData()
	{
		parent::resetFactionData();
		$this->autoClaimFaction = NULL;
	}

	public function getLastActivityMillis()
	{
		return $this->lastActivityMillis;
	}

	public function setLastActivityMillis($lastActivityMillis)
	{
		$this->lastActivityMillis = $lastActivityMillis;
		// Mark as changed
		$this->changed();
	}

	public function updateLastActivityMillis()
	{
		$this->setLastActivityMillis(time());
	}


	public function getMoney()
	{
		return Economy::get()->getMoney($this->getPlayer());
	}

	// -------------------------------------------- //
	// FIELD: powerBoost
	// -------------------------------------------- //

	public function getPlayer() : Player
	{
		return parent::getPlayer();
	}

	public function takeMoney(int $amount, bool $force = false)
	{
		return Economy::get()->takeMoney($this->getPlayer(), $amount, $force);
	}

	public function addMoney(int $amount)
	{
		//return Economy::get()->addMoney($this->getPlayer(), $amount);
	}

	// -------------------------------------------- //
	// FIELD: power
	// -------------------------------------------- //

	// MIXIN: RAW

	public function setMoney(int $amount)
	{
		//return Economy::get()->setMoney($this->getPlayer(), $amount);
	}

	public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : STRING
	{
		return RelationUtil::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
	}

	// RAW

	public function isMapAutoUpdating()
	{
		if ($this->mapAutoUpdating === NULL) return false;
		if ($this->mapAutoUpdating == FALSE) return false;
		return true;
	}

	public function setMapAutoUpdating(bool $mapAutoUpdating)
	{
		if ($this->mapAutoUpdating === $mapAutoUpdating) $target = null;

		// Detect Nochange
		if ($this->mapAutoUpdating === $mapAutoUpdating) return;

		// Apply
		$this->mapAutoUpdating = $mapAutoUpdating;

		// Mark as changed
		$this->changed();
	}


	

	// -------------------------------------------- //
	// INACTIVITY TIMEOUT                           //
	// -------------------------------------------- //

	# TODO


	// -------------------------------------------- //
	// SHORTCUTS         						    //
	// -------------------------------------------- //

	public function heal(int $hearts)
	{
		$player = $this->getPlayer();
		if ($player === NULL) {
			return;
		}
		$player->setHealth($player->getHealth() + $hearts);
	}

	public function isInOwnTerritory() : BOOL
	{
		return Plots::get()->getFactionAt($this->player) === $this->getFaction();
	}

	public function isInEnemyTerritory() : BOOL
	{
		return Plots::get()->getFactionAt($this->player)->getRelationTo($this) === Rel::ENEMY;
	}

	/**
	 * @return Position
     */
	public function getPosition() : Position
	{
		return $this->player->getPosition();
	}

	public function sendMessage(string $message)
	{
		if (!$this->isOnline()) return;
		$this->player->sendMessage($message);
	}

	public function setName(string $name) {

	}

	public function isAlive() : BOOL {
		return $this->player->isAlive();
	}

}
