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

use pocketmine\level\Level;
use pocketmine\level\Position;

use factions\permission\Permission;
use factions\flag\Flag;

interface IFaction {

	/*
	 * ----------------------------------------------------------
	 * STATUS
	 * ----------------------------------------------------------
	 */

	/**
	 * Check if faction is wilderness
	 */
	public function isNone() : bool;

	/**
	 * Check if faction is special
	 */
	public function isSpecial() : bool;
	
	/**
	 * Check if faction is not one of the default ones
	 */
	public function isNormal() : bool;

	/*
	 * ----------------------------------------------------------
	 * DATA
	 * ----------------------------------------------------------
	 */

	public function getId() : string;

	public function getName() : string;

	public function getDescription() : string;

	public function setDescription(string $description);

	public function hasDescription() : bool;

	public function getMotdMessages() : array;

	public function getMotd();

	public function setMotd(string $motd);

	public function hasMotd() : bool;

	public function getCreatedAt() : int;

	public function getLastOnline() : int;

	/*
	 * ----------------------------------------------------------
	 * HOME
	 * ----------------------------------------------------------
	 */

	/**
	 * Check if the faction home is in valid position and doesn't
	 * flout the Gameplay settings
	 */
	public function verifyHome();

	public function getHome();

	public function setHome(Position $home);

	public function hasHome() : bool;

	public function isValidHome(Position $home) : bool;

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
     * @return FPlayer|NULL
     */
	public function getLeader();

	public function getMembersWhereRole(string $role) : array;

	public function isAnyMemberOnline() : bool;

	public function isAllMembersOffline() : bool;
	
	// public function reindexMembers();

	public function promoteNewLeader(IMember $member = null);

	public function getOnlineMembers() : array;

	public function sendMessage($message);

	public function isConsideredOffline() : bool;

	public function isConsideredOnline() : bool;
	
	/*
	 * ----------------------------------------------------------
	 * INVITATION
	 * ----------------------------------------------------------
	 */

	/**
	 * @param IMember|string
	 * @return bool
	 */
	public function isInvited($member) : bool;

	public function getInvitedMembers() : array;

	public function setInvitedMembers(array $members);

	/**
	 * @param IMember|string $player
	 * @param bool $invited
	 */
	public function setInvited($player, bool $invited);

	/**
	 * @return IMember[]
	 */
	public function getOnlineInvitedMembers() : array;

	/*
	 * ----------------------------------------------------------
	 * FLAGS
	 * ----------------------------------------------------------
	 */

	public function setFlagId(array $flags);

	public function setPermissionId(array $perms);

	public function isDefaultOpen() : bool;

	public function isOpen() : bool;

	public function getFlag(string $id) : bool;

	public function setOpen(bool $open);

	/**
	 * @param array string => bool
	 */
	public function getFlags() : array;

	/**
	 * @param Flag[]
	 */
	public function setFlags(array $flags);

	public function setFlag(string $id, bool $value);

	public function isExplosionsAllowed() : bool;

	/*
	 * ----------------------------------------------------------
	 * PERMISSIONS
	 * ----------------------------------------------------------
	 */

	public function setPermissions(array $perms);

	public function setRelationPermitted(Permission $perm, string $rel, bool $permitted);

	public function getPermitted($perm) : array;

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
	 * @return string relation id
	 */
	public function getRelationWish($faction) : string;

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

	public function getAllPlots() : array;

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
	
	public function getPowerBoost() : int;

	public function setPowerBoost(int $power);

}