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

use factions\manager\Plots;
use factions\relation\Relation;
use factions\relation\RelationParticipator;
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
        // Mark as changed
        $this->changed();
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
     * SHORTCUTS
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

}
