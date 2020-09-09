<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020  Chris Prime
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
use dominate\requirement\SimpleRequirement;
use factions\command\requirement\FactionRequirement;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\manager\Plots;
use factions\permission\Permission;
use pocketmine\command\CommandSender;

class Fly extends Command
{

    public function setup() {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);
        $faction = $msender->getFaction();

        /** @var \pocketmine\Player $sender */
        $factionHere = Plots::getFactionAt($sender);
        if($factionHere->isNone() || $faction !== $factionHere && !$msender->isOverriding()) {
            return ["cant-fly-here", [
                "faction" => $factionHere->getName()
            ]];
        }

        if(!Permissions::getById(Permission::FLY)->has($msender)) {
            return ["no-perm-to-fly", [
               "faction" => $faction->getName()
            ]];
        }

        $msender->toggleFlying();

        return ["faction-fly-status", [
            "status" => $msender->isFlying() ? "<green>enabled" : "<red>disabled"
        ]];
    }

}