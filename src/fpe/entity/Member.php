<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\entity;

use fpe\engine\BoardEngine;
use fpe\manager\Factions;
use fpe\manager\Plots;
use fpe\relation\Relation;
use fpe\relation\RelationParticipator;
use pocketmine\level\Position;
use pocketmine\Player;

class Member extends OfflineMember
{

    /** @var array */
    public array $chunkPos = [0, 0];

    /** @var int $lastActivityMillis */
    protected int $lastActivityMillis = 0;

    /** @var boolean $mapAutoUpdating */
    protected bool $mapAutoUpdating = false;

    /**
     * Is player chatting with his faction mates?
     * @var bool
     */
    protected bool $fchat = false;

    /** @var string */
    protected string $factionHereId = Faction::WILDERNESS;

    /** @var boolean */
    protected bool $seeChunk = false;

    /**
     * Enabled players the ability to fly within faction claim
     *
     * @var bool
     */
    protected bool $fly = false;

    /** @var Faction|null $autoClaimFaction */
    private ?Faction $autoClaimFaction = null;

    /** @var bool $hud_visible */
    private bool $hud_visible = true;

    public function __construct(Player $player)
    {
        parent::__construct($player->getName(), compact("player"));

        $this->lastPlayed = time();
        $this->factionHereId = Plots::getFactionAt($player)->getId();
    }

    public function setFactionHereId(string $id): void
    {
        $this->factionHereId = $id;
    }

    public function toggleHUD()
    {
        $this->setHUDVisible(!$this->hasHUD());
    }

    public function setHUDVisible(bool $visible)
    {
        $this->hud_visible = $visible;

        if (!BoardEngine::enabled()) return;

        if (!$visible) {
            BoardEngine::removeBoard($this);
        } else {
            BoardEngine::sendBoard($this);
        }
    }

    public function hasHUD(): bool
    {
        return $this->hud_visible;
    }

    /**
     * @return Faction|null
     */
    public function getAutoClaimFaction()
    {
        return $this->autoClaimFaction;
    }

    /**
     * @param Faction|null
     */
    public function setAutoClaimFaction($autoClaimFaction)
    {
        $this->autoClaimFaction = $autoClaimFaction;
    }

    public function isAutoClaiming(): bool
    {
        return $this->autoClaimFaction instanceof Faction;
    }

    public function resetFactionData()
    {
        parent::resetFactionData();
        $this->autoClaimFaction = null;
    }

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false): string
    {
        return Relation::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
    }

    public function isMapAutoUpdating(): bool
    {
        if (!$this->mapAutoUpdating) return false;
        return true;
    }

    /*
     * ----------------------------------------------------------
     * F-CHAT
     * ----------------------------------------------------------
     */

    public function setMapAutoUpdating(bool $mapAutoUpdating)
    {
        if ($this->mapAutoUpdating === $mapAutoUpdating) $target = null;
        // Detect Nochange
        if ($this->mapAutoUpdating === $mapAutoUpdating) return;
        // Apply
        $this->mapAutoUpdating = $mapAutoUpdating;
    }

    public function isFactionChatOn(): bool
    {
        return $this->fchat;
    }

    public function toggleFactionChat()
    {
        $this->fchat = !$this->fchat;
    }

    /*
     * ----------------------------------------------------------
     * SHORTCUTS
     * ----------------------------------------------------------
     */

    public function setFactionChat(bool $value)
    {
        $this->fchat = $value;
    }

    public function heal(int $hearts)
    {
        if (!($player = $this->getPlayer())) return;
        $player->setHealth($player->getHealth() + $hearts);
    }

    public function isInOwnTerritory(): bool
    {
        return Plots::getFactionAt($this->player) === $this->getFaction();
    }

    public function isInEnemyTerritory(): bool
    {
        return $this->getRelationToPlot() === Relation::ENEMY;
    }

    public function getRelationToPlot(): string
    {
        return $this->getFactionHere()->getRelationTo($this);
    }

    public function getFactionHere(): Faction {
        return Factions::getById($this->factionHereId) ?? Factions::getById(Faction::WILDERNESS);
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->player->getPosition();
    }

    /*
     * ----------------------------------------------------------
     * SEECHUNK
     * ----------------------------------------------------------
     */

    public function isAlive(): bool
    {
        return $this->getPlayer()->isAlive();
    }

    public function isSeeingChunk(): bool
    {
        return $this->seeChunk;
    }

    /*
     * ----------------------------------------------------------
     * FACTION-FLY
     * ----------------------------------------------------------
     */

    public function setSeeChunk(bool $value): void
    {
        $this->seeChunk = $value;
    }

    public function toggleFlying(): void
    {
        $this->fly = !$this->fly;
        $this->updateFlying();
    }

    public function updateFlying(): void
    {
        if ($this->fly) {
            $this->player->setAllowFlight(true);
            $this->player->setFlying(true);
        } else {
            if (!$this->overriding) {
                $this->player->setFlying(false);
                $this->player->setAllowFlight(false);
            }
        }
    }

    public function setFlying(bool $value): void
    {
        $this->fly = $value;
        $this->updateFlying();
    }

    public function isFlying(): bool
    {
        return $this->fly === true;
    }

}
