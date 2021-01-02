<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Permissions;

class Power extends Command
{

    public function setup()
    {
        $this->setAliases(["powerboost", "pb"]);
        $this->addParameter(new Parameter("set|add"));

        $this->addChild(new PowerSet($this->plugin, "set", "Set players or faction's power", Permissions::SETPOWER));
    }

}