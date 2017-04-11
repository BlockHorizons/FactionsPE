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

namespace factions\command;

use factions\entity\Plot;
use pocketmine\level\Position;

class ClaimSquare extends ClaimXRadius
{

    protected $radius = 0;

    public function getPlots(Position $pos): array
    {
        $chunk = new Plot($pos);
        $chunks = [];

        $chunks[] = $chunk; // The center should come first for pretty messages

        $this->getRadius();
        $radiusZero = $this->getRadiusZero();
        for ($dx = -$radiusZero; $dx <= $radiusZero; $dx++) {
            for ($dz = -$radiusZero; $dz <= $radiusZero; $dz++) {
                $x = $chunk->x + $dx;
                $z = $chunk->z + $dz;

                $chunks[] = new Plot($x, $z, $pos->level);
            }
        }
        return $chunks;
    }

}