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

use pocketmine\event\Event;
use factions\base\EventBase;
use factions\faction\Faction;
use factions\objs\FPlayer;

class FactionLeaveEvent extends Event
{

    public static $handlerList = null;

    private $player;
    private $faction;

    public function __construct(FPlayer $player, Faction $faction)
    {
        $this->player = $player;
        $this->faction = $faction;
    }

    public function getPlayer() : FPlayer
    {
        return $this->player;
    }

    public function getFaction() : Faction
    {
        return $this->faction;
    }

}