<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\entity;

use fpe\permission\Permission;
use fpe\relation\RelationParticipator;
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

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false): string;

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

    public function addPower(int $power);

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