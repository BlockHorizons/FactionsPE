<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\entity;

use fpe\manager\Plots;
use fpe\relation\Relation;
use fpe\relation\RelationParticipator;
use pocketmine\level\Position;
use pocketmine\Player;

class Member extends OfflineMember
{

    /** @var array */
    public $chunkPos = [0, 0];

    /** @var $lastActivityMillis */
    protected $lastActivityMillis;

    /** @var boolean $mapAutoUpdating */
    protected $mapAutoUpdating = false;

    /**
     * Is player chatting with his faction mates?
     * @var bool
     */
    protected $fchat = false;

    /** @var Faction|null $autoClaimFaction */
    private $autoClaimFaction;

    /** @var bool $hud_visible */
    private $hud_visible = true;

    /** @var string */
    protected $factionHereId;

    /** @var boolean */
    protected $seeChunk = false;

    public function __construct(Player $player)
    {
        parent::__construct($player->getName(), compact("player"));

        $this->lastPlayed = time();
        $this->factionHereId = Plots::getFactionAt($player)->getId();
    }

    public function setHUDVisible(bool $visible)
    {
        $this->hud_visible = $visible;
    }

    public function toggleHUD()
    {
        $this->hud_visible = !$this->hasHUD();
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

    public function setMapAutoUpdating(bool $mapAutoUpdating)
    {
        if ($this->mapAutoUpdating === $mapAutoUpdating) $target = null;
        // Detect Nochange
        if ($this->mapAutoUpdating === $mapAutoUpdating) return;
        // Apply
        $this->mapAutoUpdating = $mapAutoUpdating;
    }

    /*
     * ----------------------------------------------------------
     * F-CHAT
     * ----------------------------------------------------------
     */

    public function isFactionChatOn(): bool
    {
        return $this->fchat;
    }

    public function toggleFactionChat()
    {
        $this->fchat = !$this->fchat;
    }

    public function setFactionChat(bool $value)
    {
        $this->fchat = $value;
    }

    /*
     * ----------------------------------------------------------
     * SHORTCUTS
     * ----------------------------------------------------------
     */

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

    public function getRelationToPlot(): string {
        return Plots::getFactionAt($this->player)->getRelationTo($this);
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->player->getPosition();
    }

    public function isAlive(): bool
    {
        return $this->getPlayer()->isAlive();
    }

    /*
     * ----------------------------------------------------------
     * SEECHUNK
     * ----------------------------------------------------------
     */

    public function isSeeingChunk(): bool
    {
        return $this->seeChunk;
    }

    public function setSeeChunk(bool $value): void
    {
        $this->seeChunk = $value;
    }

    /*
     * ----------------------------------------------------------
     * FACTION-FLY
     * ----------------------------------------------------------
     */


    /**
     * Enabled players the ability to fly within faction claim
     *
     * @var bool
     */
    protected $fly = false;

    public function updateFlying() : void {
        if($this->fly) {
            $this->player->setAllowFlight(true);
            $this->player->setFlying(true);
        } else {
            if(!$this->overriding) {
                $this->player->setFlying(false);
                $this->player->setAllowFlight(false);
            }
        }
    }

    public function toggleFlying() : void {
        $this->fly = !$this->fly;
        $this->updateFlying();
    }

    public function setFlying(bool $value) : void {
        $this->fly = $value;
        $this->updateFlying();
    }

    public function isFlying() : bool {
        return $this->fly === true;
    }

}
