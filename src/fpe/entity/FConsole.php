<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */
namespace fpe\entity;

use fpe\manager\Factions;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\relation\RelationParticipator;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class FConsole implements IMember, RelationParticipator
{

    private $console;

    public function __construct()
    {
        $this->console = new ConsoleCommandSender();
    }

    public function hasHUD(): bool
    {
        return false;
    }

    public function toggleHUD()
    {
        throw new \LogicException("Console can't have HUD");
    }

    /**
     * @return Faction
     */
    public function getFaction(): Faction
    {
        return Factions::getById($this->getFactionId());
    }

    // --------------------------- //
    //  FACTIONS
    // --------------------------- //

    public function getFactionId(): string
    {
        return Faction::NONE;
    }

    public function setFaction(Faction $faction)
    {
        $this->setFactionId($faction->getId());
    }

    public function setFactionId(string $id, bool $silent = falses)
    {
        throw new \LogicException("invalid call on console object");
    }

    // --------------------------- //
    // STRING IDENTIFIERS
    // --------------------------- //

    public function getName(): string
    {
        return "Console";
    }

    public function hasTitle(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return "";
    }

    public function getDisplayName(): string
    {
        return "Console";
    }

    // --------------------------- //
    //  POWER & ROLE
    // --------------------------- //

    public function getRole(): string
    {
        return Relation::LEADER;
    }

    public function getDefaultPower(): int
    {
        return $this->getPower();
    }

    public function getPower(bool $limit = true): int
    {
        return PHP_INT_MAX;
    }

    public function getPowerMax(): int
    {
        return $this->getPower();
    }

    public function getPowerMin(): int
    {
        return $this->getPower();
    }

    public function getPowerPerDeath(): int
    {
        return 0;
    }

    public function setRole(string $role)
    {
        throw new \LogicException("invalid call on console object");
    }

    public function setPower(int $power)
    {
        throw new \LogicException("invalid call on console object");
    }

    public function setPowerBoost(int $boost)
    {
        throw new \LogicException("invalid call on console object");
    }

    public function addPower(int $power) 
    {
        throw new \LogicException("invalid call on console object");
    }

    public function getFirstPlayed(): int
    {
        return 0;
    }

    // --------------------------- //
    //  MISC
    // --------------------------- //

    public function getLastPlayed(): int
    {
        return time();
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function isNone(): bool
    {
        return true;
    }

    public function isNormal(): bool
    {
        return false;
    }

    public function isOnline(): bool
    {
        return true;
    }

    public function isOverriding(): bool
    {
        return true;
    }

    public function hasFaction(): bool
    {
        return false;
    }

    public function hasPowerBoost(): bool
    {
        return $this->getPowerBoost() !== 0;
    }

    public function getPowerBoost(): int
    {
        return PHP_INT_MAX;
    }

    public function hasPermission(Permission $perm): bool
    {
        return true;
    }

    public function sendMessage($message)
    {
        $this->console->sendMessage("[FConsole]: " . $message);
    }

    public function getColorTo(RelationParticipator $observer): string
    {
        return TextFormat::WHITE;
    }

    public function updateLastActivity()
    {
    }

    public function getLastActivity(): int
    {
        return time();
    }

    public function resetFactionData()
    {
        throw new \LogicException("invalid call on console object");
    }

    public function isMember(): bool
    {
        return false;
    }

    public function isRecruit(): bool
    {
        return false;
    }

    public function isOfficer(): bool
    {
        return false;
    }

    public function isLeader(): bool
    {
        return false;
    }

    public function isPermitted(Permission $perm): bool
    {
        return true;
    }

    public function setOverriding(bool $value)
    {
        $this->overriding = $value;
    }

    public function getNameTag(): string
    {
        return "";
    }

    public function save()
    {

    }

    public function isFriend(RelationParticipator $observer): bool
    {
        return Relation::isFriend($this->getRelationTo($observer, true));
    }

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false): string
    {
        return Relation::NEUTRAL;
    }

    public function isEnemy(RelationParticipator $observer): bool
    {
        return Relation::isEnemy($this->getRelationTo($observer, true));
    }

    public function getPlayer()
    {
        return null;
    }

}