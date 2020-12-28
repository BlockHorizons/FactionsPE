<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\command\requirement\FactionRequirement;
use fpe\command\requirement\FactionRole;
use fpe\flag\Flag;
use fpe\manager\Members;
use fpe\relation\Relation;
use fpe\localizer\Localizer;
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