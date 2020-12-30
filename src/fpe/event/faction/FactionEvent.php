<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\event\faction;

use fpe\entity\Faction;
use pocketmine\event\Event;

class FactionEvent extends Event
{

    /** @var Faction|null */
    protected $faction;

    public function __construct(?Faction $faction)
    {
        $this->faction = $faction;
    }

    public function getFaction()
    {
        return $this->faction;
    }

}