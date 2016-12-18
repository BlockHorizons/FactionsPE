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

use factions\data\MemberData;
use factions\manager\Members;
use factions\manager\Factions;
use localizer\Localizer;
use factions\utils\Gameplay;
use factions\FactionsPE;
use factions\relation\Relation;
use factions\relation\RelationParticipator;

class OfflineMember extends MemberData implements IMember, RelationParticipator {

	public function __construct(string $name, array $data = []) {
		$sd = FactionsPE::get()->getDataProvider()->loadMember($name);
		parent::__construct(array_merge(compact("name"), $data, $sd ? $sd->__toArray() : []));
		Members::attach($this);
	}

	/*
	 * ----------------------------------------------------------
	 * FACTION
	 * ----------------------------------------------------------
	 */

	public function getFactionId() : string {
		if(!$this->factionId) return Faction::NONE;
        return $this->factionId;
	}

	public function setFactionId(string $fid) {
		// Detect Nochange
        if ($fid === $this->factionId) return;
        // Get the raw old value
        $oldFactionId = $this->factionId;
        // Apply
        $this->factionId = $fid;
        if ($oldFactionId == null) $oldFactionId = Faction::NONE;
        // Update index
        $oldFaction = Factions::getById($oldFactionId);
        $faction = $this->getFaction();
        $oldFactionIdDesc = "NULL";
        $oldFactionNameDesc = "NULL";
        if ($oldFaction != null) {
            $oldFactionIdDesc = $oldFaction->getId();
            $oldFactionNameDesc = $oldFaction->getName();
        }
        $factionIdDesc = "NULL";
        $factionNameDesc = "NULL";
        if ($faction != null) {
            $factionIdDesc = $faction->getId();
            $factionNameDesc = $faction->getName();
        }
        FactionsPE::get()->getLogger()->info(
           Localizer::trans("member-faction-changed",
                [$this->getDisplayName(), $this->getName(), $oldFactionIdDesc, $oldFactionNameDesc, $factionIdDesc, $factionNameDesc]));
        $faction->reindexMembers();
	}

	public function getFaction() : Faction {
        return Factions::getById($this->getFactionId()) ?? Factions::getById(Faction::NONE);
	}
	
	public function setFaction(Faction $faction) {
		$this->setFactionId($faction->getId());
	}
	
	public function hasFaction() : bool {
		return $this->getFaction()->isNormal();
	}
	
	public function isDefault() : bool {
		return false; # TODO
	}

	public function isNone() : bool {
		$this->factionId !== Faction::NONE;
	}

	public function resetFactionData() {
		$this->setFactionId(null);
        $this->setRole(null);
        $this->setTitle(null);
	}

	public function leave() {
		$myFaction = $this->getFaction();
        $permanent = $myFaction->getFlag(Flag::PERMANENT);
        if (count($this->getFaction()->getPlayers()) > 1)
        {
            if (!$permanent && $this->getRole() === Rel::LEADER)
            {
                $this->sendMessage(Localizer::trans('faction-leave-as-leader'));
                return;
            }
            if (!Gameplay::get("can-leave-with-negative-power", false) && $this->getPower() < 0)
            {
                $this->sendMessage(Localizer::trans('faction-leave-with-negative-power'));
                return;
            }
        }
            // Event
        $event = new PlayerMembershipChangeEvent($this, $myFaction, PlayerMembershipChangeEvent::REASON_LEAVE);
		FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) return;
		if ($myFaction->isNormal())
        {
            foreach ($myFaction->getOnlinePlayers(true) as $player)
			{
                $player->sendMessage(Localizer::trans("player-left-faction", $this->getDisplayName(), $myFaction->getName()));
            }
            FactionsPE::get()->getLogger()->info($this->getName()." left the faction: ".$myFaction->getName());
		}
		$this->resetFactionData();
		if ($myFaction->isNormal() && !$permanent && empty($myFaction->getPlayers()))
        {
            $event = new FactionDisbandEvent($this->getFactionId(), $this);
			FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
			if ( ! $event->isCancelled())
            {
                // Remove this faction
                $this->sendMessage(Localizer::trans("faction-disbanded-due-empty", [$myFaction->getName()]));
                if (Gameplay::get("log-faction-disband", true))
                {
                    FactionsPE::get()->getLogger()->info("The faction ".$myFaction->getName()." (".$myFaction->getId().") was disbanded due to the last player (".$this->getName().") leaving.");
                }
                $myFaction->detach();
            }
		}
	}
	
	/*
	 * ----------------------------------------------------------
	 * ROLE
	 * ----------------------------------------------------------
	 */

	public function setRole(string $role) {
		if($this->role === $role) return;
		$this->role = $role;
	}

	public function isRecruit() : bool {
		return $this->getRole() === Relation::RECRUIT;
	}

	public function isMember() : bool {
		return $this->getRole() === Relation::MEMBER;
	}

	public function isOfficer() : bool {
		return $this->getRole() === Relation::OFFICER;
	}

	public function isLeader() : bool {
		return $this->getRole() === Relation::LEADER;
	}

	public function getRole() : string {
		return $this->role;
	}

	/*
	 * ----------------------------------------------------------
	 * RELATION
	 * ----------------------------------------------------------
	 */

	public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : string {
		return Relation::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
	}

	public function isFriend(RelationParticipator $observer, bool $ignorePeaceful = false) : bool {
		return Relation::isFriend($this->getRelationTo($observer, $ignorePeaceful));
	}

	public function isEnemy(RelationParticipator $observer, bool $ignorePeaceful = false) : bool {
		return Relation::isEnemy($this->getRelationTo($observer, $ignorePeaceful));
	}

	public function getColorTo(RelationParticipator $observer, bool $ignorePeaceful = false) : string {
		return Relation::getColorTo($this->getRelationTo($observer, $ignorePeaceful));
	}


	/*
	 * ----------------------------------------------------------
	 * POWER
	 * ----------------------------------------------------------
	 */

	public function getPower(bool $limit = true) : int {
		$p = $this->power;
		if($limit) {
			$p = max(min($this->power, Gameplay::get('min-player-power', -10)), Gameplay::get('max-player-power', 10));
		}
		return $p - $this->getPowerBoost();
	}

	public function hasPowerBoost() : bool {
		return $this->powerBoost !== 0;
	}

	public function setPowerBoost(int $boost) {
		$this->powerBoost = $boost;
	}

	public function setPower(int $power) {
		$this->power = $power;
	}

	public function getPowerBoost() : int {
		return $this->powerBoost;
	}

	/*
	 * ----------------------------------------------------------
	 * PERMISSION
	 * ----------------------------------------------------------
	 */

	public function isPermitted(Permission $permission) : bool {
		return $this->getFaction()->isPermitted($permission, $this->getRole());
	}

	public function isOverriding() : bool {
		if ($this->overriding === NULL) return false;
        if ($this->overriding === FALSE) return false;
        if ($this->getPlayer() instanceof Player && !$this->getPlayer()->hasPermission(FactionsPE::OVERRIDE)) {
            $this->setOverriding(false);
            return false;
        }
        return true;
	}

	public function setOverriding(bool $overriding) {
		if ($overriding === false) $overriding = null;
	    if ($this->overriding === $overriding) return;
	    $this->overriding = $overriding;
	}

	/*
	 * ----------------------------------------------------------
	 * PLAYER
	 * ----------------------------------------------------------
	 */

	public function getNameTag() : string {
		return $this->player ? $this->player->getNameTag() : $this->getName();
	}

	public function isOnline() : bool {
		return $this->player ? $this->player->isOnline() : false;
	}

	public function isNormal() : bool {
		return !$this->isNone();
	}

	public function sendMessage($message) {
		if(!$this->player) return;
		$this->player->sendMessage($message);
	}

	public function getDisplayName() : string {
		return $this->isOnline() ? $this->player->getDisplayName() : $this->getName();
	}

	/*
	 * ----------------------------------------------------------
	 * LAST-ACTIVITY
	 * ----------------------------------------------------------
	 */

	public function updateLastActivity() {
		$this->setLastActivity(time());
	}
	
}