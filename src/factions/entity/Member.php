<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\entity;

use pocketmine\Player;

use factions\relation\RelationParticipator;
use factions\relation\Relation;
use factions\FactionsPE;

class Member extends OfflineMember {

	/** @var string $factionHereId */
	public $factionHereId = "";

	/** @var $lastActivityMillis */
	protected $lastActivityMillis;
	
	/** @var boolean $mapAutoUpdating */
	protected $mapAutoUpdating = false;
	
	/** @var Faction|null $autoClaimFaction */
	private $autoClaimFaction;

	public function __construct(Player $player) {
		parent::__construct($player instanceof Player ? $player->getName() : $player,
			["lastPlayed" => time()]);
	}

	/**
	 * @return Faction|null
	 */
	public function getAutoClaimFaction() {
		return $this->autoClaimFaction;
	}

	public function setAutoClaimFaction(Faction $autoClaimFaction) {
		$this->autoClaimFaction = $autoClaimFaction;
	}

	public function isAutoClaiming() : bool {
		return $this->autoClaimFaction instanceof Faction;
	}

	public function resetFactionData()
	{
		parent::resetFactionData();
		$this->autoClaimFaction = null;
	}

	public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : string {
		return Relation::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
	}

	public function isMapAutoUpdating() : bool {
		if (!$this->mapAutoUpdating) return false;
		return true;
	}

	public function setMapAutoUpdating(bool $mapAutoUpdating) {
		if ($this->mapAutoUpdating === $mapAutoUpdating) $target = null;
		// Detect Nochange
		if ($this->mapAutoUpdating === $mapAutoUpdating) return;
		// Apply
		$this->mapAutoUpdating = $mapAutoUpdating;
		// Mark as changed
		$this->changed();
	}
	
	/*
	 * ----------------------------------------------------------
	 * SHORTCUTS
	 * ----------------------------------------------------------
	 */

	public function heal(int $hearts) {
		if (!($player = $this->getPlayer())) return;
		$player->setHealth($player->getHealth() + $hearts);
	}

	public function isInOwnTerritory() : bool {
		return Plots::get()->getFactionAt($this->player) === $this->getFaction();
	}

	public function isInEnemyTerritory() : bool {
		return Plots::get()->getFactionAt($this->player)->getRelationTo($this) === Relation::ENEMY;
	}
	/**
	 * @return Position
     */
	public function getPosition() : Position
	{
		return $this->player->getPosition();
	}

	public function isAlive() : bool {
		return $this->getPlayer()->isAlive();
	}

}