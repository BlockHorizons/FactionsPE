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
use factions\permission\Permission;
use pocketmine\event\Cancellable;

class FactionPermissionChangeEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    public $member, $perm, $rel, $value, $oldValue;

    public function __construct(IMember $member, Faction $faction, Permission $perm, string $rel, bool $value)
    {
        parent::__construct($faction);

        $this->member = $member;
        $this->perm = $perm;
        $this->rel = $rel;
        $this->value = $value;
        $this->oldValue = $faction->isPermitted($perm, $rel);
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return $this->perm;
    }

    /**
     * @return string
     */
    public function getRelation()
    {
        return $this->rel;
    }

    /**
     * @return IMember
     */
    public function getMember()
    {
        return $this->member;
    }

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function getNewValue()
    {
        return $this->value;
    }

    public function setValue(bool $value)
    {
        $this->value = $value;
    }

}