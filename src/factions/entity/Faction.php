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

use pocketmine\level\Position;
use pocketmine\level\Level;

use factions\data\FactionData;
use factions\relation\RelationParticipator;
use factions\relation\Relation;
use factions\manager\Factions;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\manager\Plots;
use factions\utils\Gameplay;
use factions\permission\Permission;
use factions\flag\Flag;
use factions\FactionsPE;

use localizer\Localizer;

class Faction extends FactionData implements IFaction, RelationParticipator {

	const NONE 			= "wilderness";
	const SAFEZONE 		= "safezone";
	const WARZONE 		= "warzone";
	const WILDERNESS 	= self::NONE;

	const NAME_NONE 		= "Wilderness";
	const NAME_SAFEZONE 	= "Safezone";
	const NAME_WARZONE 		= "Warzone";
	const NAME_WILDERNESS 	= self::NAME_NONE;

	/**
	 * Faction constructor
	 * @param string 	$id
	 * @param array 	$data
	 */
	public function __construct(string $id, array $data) {
		parent::__construct(array_merge(["id" => $id], $data));
		Factions::attach($this);

		if(isset($data["creator"]) && !$this->getLeader()) {
			$this->members[Relation::LEADER] = $data["creator"];
		}
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
		return $this->getId() === self::NONE;
	}

	/**
	 * Check if faction is special
	 */
	public function isSpecial() : bool {
		return in_array($this->getId(), [self::NONE, self::WARZONE, self::SAFEZONE], true);
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
        $this->sendMessage(Localizer::translatable("home-out-of-bounds"));
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
	 * @var IMember[]
	 */
	public function getMembers() : array {
		$r = [];
		foreach ($this->getRawMembers() as $name) {
			$r[] = Members::get($name);
		}
		return $r;
	}

	public function isMember(string $member) : bool {
		return in_array($member, $this->members, true);
	}

	public function getRole(IMember $member) : string {
		if($this->isMember($member->getName())) {
			return array_search($member->getName(), $this->members);
		}
		return Relation::RECRUIT;
	}
	
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
        foreach ($this->getMembers() as $player) {
            if ($player->getRole() === $role) $ret[] = $player;
        }
        return $ret;
	}

	public function isAnyMemberOnline() : bool {
		 return !$this->isAllMembersOffline();
	}

	public function isAllMembersOffline() : bool {
		return count($this->getOnlineMembers()) == 0;
	}


	public function promoteNewLeader(IMember $leader = null) {
		if ($this->isNone()) return;
        if ($this->getFlag(Flag::PERMANENT) && Gameplay::get("faction.disable-permanent-leader-promotion", true)) return;
        if($leader && !$leader->hasFaction() or $leader && $leader->getFactionId() !== $this->getFactionId()) return;
        $oldLeader = $this->getLeader();
        // get list of officers, or list of normal members if there are no officers
        $replacements = $leader instanceof Member ? [$leader] : $this->getMembersWhereRole(Relation::OFFICER);
        if (empty($replacements)) {
            $replacements = $this->getMembersWhereRole(Relation::MEMBER);
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
            if (Gameplay::get("log.faction-disband", true)) {
                FactionsPE::get()->getLogger()->info("The faction " . $this->getName() . " (" . $this->getId() . ") has been disbanded since it has no members left.");
            }
            $this->disband();
        } else {
            // promote new faction leader
            if ($oldLeader != null) {
                $oldLeader->setRole(Rel::MEMBER);
            }
            $replacements[0]->setRole(Rel::LEADER);
            $this->sendMessage(Localizer::translatable("faction-new-leader", [$oldLeader == null ? "" : $oldLeader->getName(), $replacements[0]->getName()]));
            if(Gameplay::get('log.faction-new-leader', true)) {
            	FactionsPE::get()->getLogger()->info("Faction " . $this->getName() . " (" . $this->getId() . ") leader was removed. Replacement leader: " . $replacements[0]->getName());
        	}
        }
	}

	public function disband() {
		foreach (Members::getAllOnline() as $player) {
	        $player->sendMessage(Localizer::translatable("faction-disbanded", [$this->getName()]));
	    }
	    Factions::detach($this);
	    FactionsPE::get()->getDataProvider()->deleteFaction($this->getId());
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

	public function isConsideredOffline() : bool {
		return $this->isAllMembersOffline();
	}

	public function isConsideredOnline() : bool {
		return !$this->isConsideredOffline();
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

	public function setFlagId(array $target) {
        // Detect Nochange
        if ($this->flags === $target) return;
        // Apply
        $this->flags = $target;
	}

	public function setPermissionId(array $target) {
        // Detect Nochange
        if ($this->perms === $target) return;
        // Apply
        $this->perms = $target;
	}

	public function isDefaultOpen() : bool {
        return Flag::getFlagOpen()->isStandard();
    }

	public function isOpen() : bool {
		return $this->getFlag(Flag::OPEN);
	}

	public function getFlag(string $id) : bool {
        $ret = isset($this->flags[$id]) ? $this->flags[$id] : null;
        if ($ret !== null) return $ret;
        $flag = Flag::getById($id);
        if ($flag === null) throw new \Exception("undefined flag '$id'");
        return $flag->isStandard();
	}

	public function setOpen(bool $open) {
		$this->setFlag(Flag::OPEN, $open);
	}

	public function setFlag(string $id, bool $value) {
		$this->setFlagId([$id => $value]);
	}

	/**
	 * @param array string => bool
	 */
	public function getFlags() : array {
		$r = [];
		foreach (Flag::getAll() as $flag) {
			$r[$flag->getId()] = $this->flags[$flag->getId()] ?? $flag->isStandard();
		}
		return $r;
	}

	/**
	 * @param Flag[]
	 */
	public function setFlags(array $flags) {
		$flagIds = [];
		foreach ($flags as $flag) {
			$flagIds[$flag->getId()] = $flag->isStandard();
		}
	}

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

	public function setPermissions(array $perms) {
		$permIds = [];
        foreach ($perms as $key => $value) {
            $permIds[$key] = $value;
        }
        $this->setPermissioIds($permIds);
	}

	public function setRelationPermitted(Permission $perm, string $rel, bool $permitted) {
		$perms = $this->getPermissions();
        $relations = $this->getPermitted($perm);
        if ($permitted and !$this->isPermitted($perm, $rel)) {
            $relations[] = $rel;
        } else {
            unset($relations[array_search($rel, $relations, true)]);
        }
        $perms[$perm->getId()] = $relations;
        $this->setPermissions($perms);
        $this->save();
	}

	/**
	 * Get array of relations that has Permission
	 * @param Permission|string
	 * @return array
	 */
	public function getPermitted($perm) : array {
		$id = $perm instanceof Permission ? $perm->getId() : $perm;
        if(isset($this->getPermissions()[$id])) $rels = $this->getPermissions()[$id];
        return $rels ?? ($perm instanceof Permission ? $perm->getStandard() : []);
	}

	public function isPermitted() : bool {
        return in_array($rel, $this->getPermitted($perm), true);
	}

	public function setPermittedRelations(Permission $perm, array $rels) {
        $this->setPermissions([$perm->getId() => $rels]);
	}

	public function getPermissions() : array {
        $r = [];
        foreach (Permissions::getAll() as $perm) {
            $r[$perm->getId()] = $this->perms[$perm->getId()] ?? $perm->getStandard();
        }
        return $r;
	}

	/*
	 * ----------------------------------------------------------
	 * RELATIONS
	 * ----------------------------------------------------------
	 */

	/**
     * Returns list of Factions where relations with this faction is equal to $rel
     * @param string $rel
     * @return Faction[]
     */
	public function getFactionsWhereRelation(string $relation) : array {
		$r = [];
        foreach(Factions::getAll() as $f) {
            if($f === $this) continue;
            if($f->getRelationTo($this) === $rel) $r[] = $f;
        }
        return $r;
	}

	/**
	 * @param IFaction|string $faction
	 * @param string $rel
	 * @return string relation id
	 */
	public function getRelationWish($faction) : string {
		$fid = $faction instanceof Faction ? $faction->getId() : $faction;
        return $this->relationWishes[$fid] ?? Relation::NEUTRAL;
	}

	/**
	 * @param IFaction|string
	 * @param string $rel id
	 *
	 */
	public function setRelationWish($faction, string $rel) {
		$fid = $faction instanceof Faction ? $faction->getId() : $faction;
		if(!$rel || $rel === Relation::NEUTRAL) {
			unset($this->relationWishes[array_search($fid, $this->relationWishes)]);
		} else {
			$this->relationWishes[$fid] = $rel;
		}
	}

	/**
	 * @return array string => string (faction id => relation id)
	 */
	public function getRelationWishes() : array {
		return $this->relationWishes;
	}

	public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : string {
		return Relation::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
	}

	public function isFriend(RelationParticipator $observer) : bool {
		return Relation::isFriend($this->getRelationTo($observer));
	}

	public function isEnemy(RelationParticipator $observer) : bool {
		return Relation::isEnemy($this->getRelationTo($observer));
	}

	public function getColorTo(RelationParticipator $observer) : string {
		return Relation::getColorOfThatToMe($this, $observer);
	}
	/*
	 * ----------------------------------------------------------
	 * PLOTS
	 * ----------------------------------------------------------
	 */

	public function getAllPlots() : array {
		return Plots::get()->getFactionPlots($this);
	}

	public function getPlotsCountInLevel(Level $level) : int {
		return count($this->getPlotsInLevel());
	}

	public function getPlotsInLevel(Level $level) : array {
		return Plots::get()->getPlotsInLevel($level, $this);
	}

	public function hasLandInflation() : bool {
		return $this->getPlotsCountInLevel() > $this->getPower();
	}

	public function getLandCount() : int {
		return count($this->getAllPlots());
	}

	/*
	 * ----------------------------------------------------------
	 * POWER
	 * ----------------------------------------------------------
	 */

	public function getPower() : int {
		if ($this->getFlag(Flag::INFINITY_POWER)) return PHP_INT_MAX;
        $ret = 0;
        foreach ($this->getPlayers() as $fplayer) {
            $ret += $fplayer->getPower();
        }
        $ret += $this->getPowerBoost();
        $max = Gameplay::get('max-faction-power', null);
        if($max && $max > 0) $ret = min($ret, $max);
        return $ret;
	}

	public function __toString() : string {
		return $this->getName()." (".$this->getId().")";
	}

	public static function createId() : string {
		do { // Dangerous?
			$id = implode("-", array_values(array_map(function($el) {
				return implode("", $el);
			}, array_chunk(str_split(md5(mt_rand(0, PHP_INT_MAX)) . md5(mt_rand(0, PHP_INT_MAX)) ), 4))));
		} while (Factions::getById($id));
		return substr($id, 0, 4 * 4 + 3);
	}

}