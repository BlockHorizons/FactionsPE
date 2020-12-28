<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\command\parameter\FactionParameter;
use fpe\manager\Permissions;
use pocketmine\command\CommandSender;

class Balance extends Command
{

    public function setup()
    {
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self")->setPermission(Permissions::MONEY_BALANCE_ANY));

        //$this->addRequirement(new FactionPermission(Permissions::getById(Permission::MONEY_BALANCE)));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $faction = $this->getArgument(0);

        return ["faction-balance", ["balance" => $faction->getBank()]];
    }

}
