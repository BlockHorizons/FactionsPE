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

use factions\command\parameter\FactionParameter;
use factions\command\parameter\PermissionParameter;
use factions\permission\Permission;
use factions\manager\Pemrissions;
use factions\utils\Text;
use factions\utils\Pager;

use pocketmine\command\CommandSender;

use localizer\Localizer;

class PermShow extends Command {

    public function setup() {
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new PermissionParameter("perm", PermissionParameter::ANY))->setDefaultValue("all"));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args) {
        $perms = $this->getArgument(1);
        if(!is_array($perms)) $perms = [$perms];

        $faction = $this->getArgument(2);
        $page = $this->getArgument(0);

        $pager = new Pager("perm-show-header", $page, 5, $perms, $sender, function(Permission $perm, int $i, CommandSender $sender) use ($faction){
            return Text::parse($perm->getStateInfo($faction->getPermitted($perm), true));
        });
        $pager->stringify();

        $sender->sendMessage(Text::titleize(Localizer::translatable($pager->getHeader(), [$pager->getPage(), $pager->getMax(), $faction->getName()])));

        $sender->sendMessage(Permission::getStateHeaders());
        foreach ($pager->getOutput() as $line) $sender->sendMessage($line);
        
        return true;
    }
}