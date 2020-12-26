<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\manager\Permissions;

class Power extends Command
{

    public function setup()
    {
        $this->setAliases(["powerboost", "pb"]);
        $this->addParameter(new Parameter("set|add"));

        $this->addChild(new PowerSet($this->plugin, "set", "Set players or faction's power", Permissions::SETPOWER));
    }

}