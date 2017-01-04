<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;

use factions\command\subcommand\childs\PermListChild;
use factions\command\subcommand\childs\PermSetChild;
use factions\command\subcommand\childs\PermShowChild;
use factions\manager\Permissions;
use factions\FactionsPE;

class Perm extends Command {
	
	public function setup() {
		$this->addParameter(new Parameter("list|show|set", Parameter::TYPE_STRING));
		
		$plugin = $this->getPlugin();
		$this->addChild(new PermList($plugin, "list", "List available faction permissions",
            Permissions::PERM_LIST));
		$this->addChild(new PermShow($plugin, "show", "See permitted relations for permission", Permissions::PERM_SHOW, ["sw"]));
		$this->addChild(new PermSet($plugin, "set", "Set faction permission", Permissions::PERM_SET));
	}

}