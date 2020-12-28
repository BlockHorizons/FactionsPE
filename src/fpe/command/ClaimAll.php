<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use pocketmine\command\CommandSender;
use pocketmine\level\Position;

class ClaimAll extends ClaimX
{

    public function perform(CommandSender $sender, $label, array $args)
    {
        $this->sender = $sender;

        // Args
        $newFaction = $this->getArgument($this->getFactionArgIndex());
        Plots::claimAll($newFaction, $sender->getLevel());
        return ["", [
            "world" => $sender->getLevel(),
            ""
        ]];
    }

    public function getPlots(Position $position): array
    {

    }
}