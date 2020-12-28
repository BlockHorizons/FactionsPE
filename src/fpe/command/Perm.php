<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\command\subcommand\childs\PermListChild;
use fpe\command\subcommand\childs\PermSetChild;
use fpe\command\subcommand\childs\PermShowChild;
use fpe\manager\Permissions;

class Perm extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("list|show|set", Parameter::TYPE_STRING));

        $plugin = $this->getPlugin();
        $this->addChild(new PermList($plugin, "list", "List available faction permissions",
            Permissions::PERM_LIST));
        $this->addChild(new PermShow($plugin, "show", "See permitted relations for permission", Permissions::PERM_SHOW, ["sw"]));
        $this->addChild(new PermSet($plugin, "set", "Set faction permission", Permissions::PERM_SET));
    }

}