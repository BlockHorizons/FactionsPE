<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\subcommand\childs\PermListChild;
use BlockHorizons\FactionsPE\command\subcommand\childs\PermSetChild;
use BlockHorizons\FactionsPE\command\subcommand\childs\PermShowChild;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Permissions;

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