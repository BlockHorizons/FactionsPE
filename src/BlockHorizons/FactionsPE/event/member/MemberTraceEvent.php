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

namespace BlockHorizons\FactionsPE\event\member;

use BlockHorizons\FactionsPE\entity\Member;
use BlockHorizons\FactionsPE\entity\Plot;

/**
 * When player walks into another chunk
 */
class MemberTraceEvent extends MemberEvent
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var Plot */
    public $from, $to;

    public function __construct(Member $member, Plot $from, Plot $to)
    {
        parent::__construct($member);
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Returns true if {@link $this->from} owner is the same from {@link $this->to}
     */
    public function sameOwner(): bool
    {
        return $this->getFrom()->getOwnerFaction() === $this->getTo()->getOwnerFaction();
    }

    public function getFrom(): PLot
    {
        return $this->from;
    }

    public function getTo(): Plot
    {
        return $this->to;
    }

    public function membersLand(): bool
    {
        return $this->getTo()->getOwnerFaction() === $this->getMember()->getFaction();
    }

}