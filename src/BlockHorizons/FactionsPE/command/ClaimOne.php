<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\entity\Plot;
use pocketmine\level\Position;

class ClaimOne extends ClaimX
{

    public function getPlots(Position $pos): array
    {
        return [new Plot($pos)];
    }

}