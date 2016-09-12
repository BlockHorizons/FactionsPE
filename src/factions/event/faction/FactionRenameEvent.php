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


use factions\base\EventBase;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionRenameEvent extends EventBase implements Cancellable
{

    private $factionId;
    private $renamer;
    private $newName;
    private $oldName;

    public function __construct($factionId, FPlayer $renamer, $newName, $oldName)
    {
        $this->factionId = $factionId;
        $this->renamer = $renamer;
        $this->newName = $newName;
        $this->oldName = $oldName;
    }

    public function getFaction() : Faction
    {
        return Factions::_getFactionById($this->factionId);
    }

    public function getFactionId() : string
    {
        return $this->factionId;
    }

    public function getPlayer() : FPlayer
    {
        return $this->renamer;
    }

    public function getNewName() : string
    {
        return $this->newName;
    }

    public function getOldName() : string
    {
        return $this->oldName;
    }

}