<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\entity\Plot;
use pocketmine\level\Position;

class ClaimOne extends ClaimX
{

    public function getPlots(Position $pos): array
    {
        return [new Plot($pos)];
    }

}