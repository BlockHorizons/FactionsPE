<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event;

use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\entity\IMember;
use BlockHorizons\FactionsPE\entity\Plot;
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