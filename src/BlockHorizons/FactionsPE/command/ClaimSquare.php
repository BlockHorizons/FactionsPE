<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\entity\Plot;
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