<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\event\faction;

use factions\objs\FPlayer;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

class FactionCreateEvent extends Event implements Cancellable
{

    public static $handlerList = null;

    /** @var string $factionName */
    private $factionName;
    
    /**
     * Creator usually is the leader of faction
     * So Faction::getLeader() === FactionCreateEvent::getCreator()
     *
     * @var FPlayer $creator
     */
    private $creator;

    /**
     * FactionCreateEvent constructor.
     * @param string $factionName
     * @param FPlayer $creator
     */
    public function __construct($factionName, FPlayer $creator)
    {
        $this->factionName = $factionName;
        $this->creator = $creator;
    }

    public function getName() : string
    {
        return $this->factionName;
    }

    /**
     * @return FPlayer
     */
    public function getCreator()
    {
        return $this->creator;
    }

}