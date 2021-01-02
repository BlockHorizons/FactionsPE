<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\data\provider;

use BlockHorizons\FactionsPE\data\FactionData;
use BlockHorizons\FactionsPE\entity\IFaction;

trait FactionFilePath
{

    /**
     * @param IFaction|FactionData|string $member
     * @param $ext with fullstop
     */
    public function getFactionFilePath($faction, string $ext)
    {
        $name = strtolower(trim(is_string($faction) ? $faction : $faction->getId()));
        return $this->getMain()->getDataFolder() . "factions/" . $name . $ext;
    }

}