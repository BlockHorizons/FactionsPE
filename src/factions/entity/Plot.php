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

use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\manager\Plots;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;

class Plot extends Position
{

    /**
     * Plot constructor.
     * @param Position|int $x
     * @param int $z
     * @param Level|null $level
     */
    public function __construct($x, $z = 0, Level $level = null)
    {
        if ($x instanceof Position) {
            $this->x = $x->x >> 4;
            $this->z = $x->z >> 4;
            $this->level = $x->level;
        } else {
            $this->x = $x;
            $this->z = $z;
            $this->level = $level;
        }
    }

    public static function fromHash(string $hash): Plot
    {
        list($x, $z, $level) = explode(":", $hash);
        $level = FactionsPE::get()->getServer()->getLevelByName($level);
        $plot = new Plot($x, $z, $level);
        return $plot;
    }

    public function unclaim(IMember $member = null, bool $silent = null)
    {
        Plots::unclaim($this, $member, $silent ?? $member ? false : true);
    }

    public function claim(Faction $faction, IMember $player = null)
    {
        if ($player === null) {
            $player = $faction->getLeader();
        }
        return Plots::claim($faction, $this, $player, false);
    }

    public function isClaimed()
    {
        return $this->getOwnerFaction()->isNone() === false;
    }

    public function getOwnerFaction(): Faction
    {
        return Plots::getFactionAt($this);
    }

    public function getOwnerId(): string
    {
        return $this->getOwnerFaction()->getId();
    }

    public function getPosition(): Position
    {
        return new Position($this->x << 4, 0, $this->z << 4, $this->level);
    }

    public function hash(): string
    {
        return Plots::hash($this);
    }

    public function addX($x): Plot
    {
        $x = ($x instanceof Vector3) ? $x->x : $x;
        $this->x += $x;
        return $this;
    }

    public function addZ($z): Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->z += $z;
        return $this;
    }

    public function subtractX($x): Plot
    {
        $this->subtract($x);
        return $this;
    }

    public function subtractZ($z): Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->subtract(0, 0, $z);
        return $this;
    }

    /**
     * Returns chunk that plot sits on or null if invalid plot or level is not loaded
     * @return Chunk|null
     */
    public function getChunk(): ?Chunk 
    {
        if($this->level) {
            return $this->level->getChunk($this->x, $this->z);
        }
        return null;
    }

}
