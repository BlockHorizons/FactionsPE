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

use factions\permission\Permission;
use pocketmine\Player;

interface IMember
{

    /*
     * ----------------------------------------------------------
     * LAST-ACTIVITY
     * ----------------------------------------------------------
     */

    public function updateLastActivity();

    public function getLastActivity(): int;

    /*
     * ----------------------------------------------------------
     * FACTION
     * ----------------------------------------------------------
     */

    public function getFactionId(): string;

    /**
     * $silent is for internal use ONLY!
     * @param string $fid faction id
     * @param bool $silent = false
     */
    public function setFactionId(string $fid, bool $silent = false);

    public function getFaction(): Faction;

    public function setFaction(Faction $faction);

    public function hasFaction(): bool;

    public function isDefault(): bool;

    public function isNone(): bool;

    public function resetFactionData();

    /*
     * ----------------------------------------------------------
     * ROLE
     * ----------------------------------------------------------
     */

    public function setRole(string $role);

    public function isRecruit(): bool;

    public function isMember(): bool;

    public function isOfficer(): bool;

    public function isLeader(): bool;

    public function getRole(): string;


    /*
     * ----------------------------------------------------------
     * POWER
     * ----------------------------------------------------------
     */

    public function getPower(bool $limit = true): int;

    public function getDefaultPower(): int;

    public function getPowerMax(): int;

    public function getPowerMin(): int;

    public function getPowerPerDeath(): int;

    public function hasPowerBoost(): bool;

    public function setPowerBoost(int $boost);

    public function setPower(int $power);

    public function getPowerBoost(): int;

    /*
     * ----------------------------------------------------------
     * PERMISSION
     * ----------------------------------------------------------
     */

    public function isPermitted(Permission $permission): bool;

    public function isOverriding(): bool;

    public function setOverriding(bool $value);

    /*
     * ----------------------------------------------------------
     * PLAYER
     * ----------------------------------------------------------
     */

    /**
     * @return Player|null
     */
    public function getPlayer();

    public function getFirstPlayed(): int;

    public function getLastPlayed(): int;

    public function getName(): string;

    public function getNameTag(): string;

    public function isOnline(): bool;

    public function isNormal(): bool;

    public function sendMessage($message);

    public function getTitle(): string;

    public function getDisplayName(): string;

    public function save();

}