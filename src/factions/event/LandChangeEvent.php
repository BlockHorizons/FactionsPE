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

namespace factions\event;

use factions\entity\Faction;
use factions\entity\IMember;
use factions\entity\Plot;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

/**
 * When plot changes an owner
 */
class LandChangeEvent extends Event implements Cancellable
{

    const CLAIM = 1;
    const UNCLAIM = 2;

    public static $handlerList = null;

    /** @var Faction */
    private $faction;
    /** @var IMember|null */
    private $player;
    /** @var int */
    private $changeType;
    /** @var Plot */
    private $plot;

    /**
     * LandChangeEvent constructor.
     *
     * @param int $changeType
     * @param Plot $plot
     * @param Faction $faction new owner
     * @param IMember|null $player
     */
    public function __construct(int $changeType, Plot $plot, Faction $faction, IMember $player = null)
    {
        $this->faction = $faction;
        $this->changeType = $changeType;
        $this->player = $player;
        $this->plot = $plot;
    }

    /**
     * @return Faction
     */
    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function getOldFactionId(): string
    {
        return $this->getOldFaction()->getId();
    }

    public function getOldFaction(): Faction
    {
        return $this->plot->getOwnerFaction();
    }

    public function getFactionId(): string
    {
        return $this->faction->getId();
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function getChangeType(): int
    {
        return $this->changeType;
    }

    public function getPlot(): Plot
    {
        return $this->plot;
    }

    public function setPlot(Plot $plot)
    {
        $this->plot = $plot;
    }

}