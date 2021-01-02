<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\requirement\FactionRequirement;
use BlockHorizons\FactionsPE\command\requirement\FactionRole;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\flag\Flag;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\relation\Relation;
use pocketmine\command\CommandSender;

class Close extends Command
{

    public function setup()
    {
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
        $this->addRequirement(new FactionRole(Relation::LEADER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);
        $faction = $msender->getFaction();
        if (!$faction->getFlag(Flag::OPEN)) {
            $sender->sendMessage(Localizer::translatable("faction-already-closed"));
            return true;
        }
        $faction->setFlag(Flag::OPEN, false);
        $sender->sendMessage(Localizer::translatable("faction-closed"));
        return true;
    }

}