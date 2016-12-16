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

use factions\data\FactionData;
use factions\relation\RelationParticipator;
use factions\managers\Factions;
use factions\managers\Members;

class Faction extends FactionData implements IFaction, RelationParticipator {

	const ID_NONE 		= "wilderness";
	const ID_SAFEZONE 	= "safezone";
	const ID_WARZONE 	= "warzone";
	const ID_WILDERNESS = self::ID_NONE;

	/**
	 * Faction constructor
	 * @param string 	$id
	 * @param array 	$data
	 */
	public function __construct(string $id, array $data) {
		parent::__construct(array_merge(["id" => $id], $data));
		Factions::attach($this);
	}
	

	/*
	 * ----------------------------------------------------------
	 * STATUS
	 * ----------------------------------------------------------
	 */

	/**
	 * Check if faction is wilderness
	 */
	public function isNone() : bool {
		return $this->getId() === self::ID_NONE;
	}

	/**
	 * Check if faction is special
	 */
	public function isSpecial() : bool {
		return in_array($this->getId(), [self::ID_NONE, self::ID_WARZONE, self::ID_SAFEZONE], true);
	}
	
	/**
	 * Check if faction is not one of the default ones
	 */
	public function isNormal() : bool {
		return !$this->isSpecial();
	}

	/*
	 * ----------------------------------------------------------
	 * DATA
	 * ----------------------------------------------------------
	 */

	public function getMotdMessages() : array {
		$ret = [];

		$ret[] = Text::titleize($this->getName() . " - Message of the Day");
		$ret[] = Text::parse("@i".$this->getMotd() ?? " ~");
		$ret[] = "";

		return $ret;
	}

	public function getLastOnline() : int {
		$t = 0;
		foreach ($this->getMembers() as $member) {
			if(($l = $this->getLastPlayed()) > $t) $t = $l;
		}
		return $t;
	}

	/*
	 * ----------------------------------------------------------
	 * HOME
	 * ----------------------------------------------------------
	 */

	/**
	 * Check if the faction home is in valid position and doesn't
	 * flout the Gameplay settings
	 */
	public function verifyHome() {
		if($this->home == null) return;
        if ($this->isValidHome($this->home)) return;
        $this->home = null;
        $this->save();
        $this->sendMessage(new Translatable("home-out-of-bounds"));
	}

	public function isValidHome(Position $home) : bool {
		if ($home === null) return false;
        if (!$home instanceof Position) return false;
        if (!Gameplay::get("homes-must-be-in-claimed-territories", true)) return true;
        if (Plots::getFactionAt($home) === $this) return true;
        return false;
	}

	/*
	 * ----------------------------------------------------------
	 * MEMBERS
	 * ----------------------------------------------------------
	 */
	
	/**
     * Returns this Faction's leader, if returned null and this faction isn't special
     * Then this object is considered invalid and must be destroyed or new Leader has to be attached.
     * As invalid faction object in runtime may cause an unexpected behaviour
     * 
     * @return IMember|NULL
     */
	public function getLeader() {
        $ret = $this->getMembersWhereRole(Relation::LEADER);
        if (empty($ret)) return null;
        return $ret[0];
	}

	public function getMembersWhereRole(string $role) : array {
		$ret = [];
        foreach ($this->getPlayers() as $player) {
            if ($player->getRole() === $rel) $ret[] = $player;
        }
        return $ret;
	}

	public function isAnyMemberOnline() : bool {
		 return !$this->isAllMembersOffline();
	}

	public function isAllMembersOffline() : bool {
		return count($this->getPlayersWhereOnline(true)) == 0;
	}
	
	public function reindexMembers() {
		$this->members = [];

        $factionId = $this->getId();
        if ($factionId == null) return;
        foreach (Members::getAll() as $member) {
            if ($player->getFactionId() !== $factionId) continue;
            $this->members[] = $member;
        }
	}

	public function promoteNewLeader(IMember $leader = null) {
		if ($this->isNone()) return;
        if ($this->getFlag(Flag::PERMANENT) && Gameplay::get("permanent-factions-disable-leader-promotion", true)) return;
        if ($leader and !$leader->hasFaction() or $leader->getFaction() !== $this) return;
        $oldLeader = $this->getLeader();
        // get list of officers, or list of normal members if there are no officers
        $replacements = $leader instanceof Member ? [$leader] : $this->getPlayersWhereRole(Relation::OFFICER);
        if (empty($replacements)) {
            $replacements = $this->getPlayersWhereRole(Relation::MEMBER);
        }
        if (empty($replacements)) {
            // faction leader is the only member; one-man faction
            if ($this->getFlag(Flag::PERMANENT)) {
                if ($oldLeader != null) {
                    // TODO: Where is the logic in this? Why MEMBER? Why not LEADER again? And why not OFFICER or RECRUIT?
                    $oldLeader->setRole(Relation::MEMBER);
                }
                return;
            }
            // no members left and faction isn't permanent, so disband it
            if (Settings::get("logFactionDisband", true)) {
                FactionsPE::get()->getLogger()->info("The faction " . $this->getName() . " (" . $this->getId() . ") has been disbanded since it has no members left.");
            }
            foreach (FPlayer::getAllOnline() as $player) {
                $player->sendMessage(Text::parse("<i>The faction %var0<i> was disbanded.", $this->getName()));
            }
            $this->detach();
        } else {
            // promote new faction leader
            if ($oldLeader != null) {
                $oldLeader->setRole(Rel::MEMBER);
            }
            /** @var FPlayer[] $replacements */
            $replacements[0]->setRole(Rel::LEADER);
            $this->sendMessage(Text::parse("<i>Faction leader <h>%var0<i> has been removed. %var1<i> has been promoted as the new faction leader.", $oldLeader == null ? "" : $oldLeader->getName(), $replacements[0]->getName()));
            FactionsPE::get()->getLogger()->info("Faction " . $this->getName() . " (" . $this->getId() . ") leader was removed. Replacement leader: " . $replacements[0]->getName());
        }
	}

	public function getOnlineMembers() : array {
		$ret = [];
		foreach ($this->getMembers() as $member) {
			if($member->isOnline()) $ret[] = $member;
		}
		return $ret;
	}

	public function sendMessage($message) {
		foreach ($this->getOnlinePlayers() as $player) {
            $player->sendMessage($message);
        }
	}

	public function isFactionConsideredOffline() : bool {
		return $this->isAllMembersOffline();
	}

	public function isFactionConsideredOnline() : bool {
		return !$this->isFactionConsideredOffline();
	}
	
	/*
	 * ----------------------------------------------------------
	 * INVITATION
	 * ----------------------------------------------------------
	 */

	/**
	 * @param IMember|string
	 * @return bool
	 */
	public function isInvited($member) : bool {
		$member = $member instanceof IMember ? $member->getName() : $member;
		return in_array(strtolower(trim($member)), $this->invitedPlayers, true);
	}

	/**
	 * @return string[]
	 */
	public function getInvitedMembers() : array {
		$r = [];
		foreach ($this->getInvitedPlayers() as $name) {
			$r[] = Members::get($name, true);
		}
		return $r;
	}

	public function setInvitedMembers(array $members) {
		foreach ($members as $member) {
			$this->invitedPlayers[] = strtolower(trim($member->getName()));			
		}
	}

	/**
	 * @param IMember|string $player
	 * @param bool $invited
	 */
	public function setInvited($player, bool $invited) {
		if(!$invited)
			unset($this->invitedPlayers[array_search($player instanceof IMember ? $player->getName() : $player, $this->invitedPlayers)]);
		else
			$this->invitedPlayers[] = strtolower(trim($player instanceof IMember ? $player->getName() : $player));
	}

	/**
	 * @return IMember[]
	 */
	public function getOnlineInvitedMembers() : array {
		$r = [];
		foreach ($this->getInvitedPlayers() as $name) {
			$m = Members::get($name, false);
			if(!$m || !$m->isOnline())
				$r[] = $m;
		}
		return $r;
	}

	/*
	 * ----------------------------------------------------------
	 * FLAGS
	 * ----------------------------------------------------------
	 */

	public function setFlagsId(array $flags);

	public function setPermissionsId(array $perms);

	public function isDefaultOpen() : bool;

	public function isOpen() : bool;

	public function getFlag(string $id) : bool;

	public function setOpen(string $id, bool $open);

	/**
	 * @param array string => bool
	 */
	public function getFlags() : array;

	/**
	 * @param Flag[]
	 */
	public function setFlags(array $flags);

	/**
     * Returns true if explosion can occur on this Faction's land
     * @return bool
     */
	public function isExplosionsAllowed() : bool {
		$explosions = $this->getFlag(Flag::EXPLOSIONS);
        $offlineexplosions = $this->getFlag(Flag::OFFLINE_EXPLOSIONS);
        if ($explosions && $offlineexplosions) return true;
        if (!$explosions && !$offlineexplosions) return false;
        $online = $this->isFactionConsideredOnline();
        return ($online && $explosions) || (!$online && $offlineexplosions);
	}

	/*
	 * ----------------------------------------------------------
	 * PERMISSIONS
	 * ----------------------------------------------------------
	 */

	public function setPermissions(array $perms);

	public function setRelationPermitted(Permission $perm, string $rel, bool $permitted);

	public function getPermitted(Permission $perm) : array;

	public function isPermitted() : bool;

	public function setPermittedRelations(Permission $perm, array $rels);

	public function getPermissions() : array;

	/*
	 * ----------------------------------------------------------
	 * RELATIONS
	 * ----------------------------------------------------------
	 */

	public function getFactionsWhereRelation(string $relation) : array;

	/**
	 * @param IFaction|string $faction
	 * @param int $rel
	 * @return string relation id
	 */
	public function getRelationWish($faction, string $rel) : string;

	/**
	 * @param IFaction|string
	 * @param int $rel id
	 *
	 */
	public function setRelationWish($faction, string $rel);

	/**
	 * @return array string => string (faction id => relation id)
	 */
	public function getRelationWishes() : array;

	/*
	 * ----------------------------------------------------------
	 * PLOTS
	 * ----------------------------------------------------------
	 */

	public function getPlotsCountInLevel(Level $level) : int;

	public function getPlotsInLevel(Level $level) : array;

	public function hasLandInflation() : bool;

	public function getLandCount() : int;

	/*
	 * ----------------------------------------------------------
	 * POWER
	 * ----------------------------------------------------------
	 */

	public function getPower() : int;

	public function getMaxPower() : int;

	public function getPowerBoost() : int;

	public function setPowerBoost(int $power);

}