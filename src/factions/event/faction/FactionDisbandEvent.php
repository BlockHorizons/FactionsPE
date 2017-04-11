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

namespace factions\event\faction;

use factions\entity\Faction;
use factions\entity\IMember;
use pocketmine\event\Cancellable;

class FactionDisbandEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var IMember */
    public $member;

    /** @var int */
    public $reason;

    public function __construct(IMember $member, Faction $faction, $reason = Faction::DISBAND_REASON_UNKNOWN)
    {
        parent::__construct($faction);
        $this->member = $member;
        $this->reason = $reason;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getReason()
    {
        return $this->reason;
    }

}