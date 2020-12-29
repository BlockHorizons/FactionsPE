<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\command\requirement\FactionRequirement;
use fpe\command\requirement\FactionRole;
use fpe\dominate\Command;
use fpe\flag\Flag;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use fpe\relation\Relation;
use pocketmine\command\CommandSender;

class Open extends Command
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
        if ($faction->getFlag(Flag::OPEN)) {
            $sender->sendMessage(Localizer::translatable("faction-already-opened"));
            return true;
        }
        $faction->setFlag(Flag::OPEN, true);
        $sender->sendMessage(Localizer::translatable("faction-opened"));
        return true;
    }

}