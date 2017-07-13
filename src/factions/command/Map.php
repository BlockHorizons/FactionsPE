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
use factions\manager\Members;
use factions\utils\Collections;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Map extends Command
{

    public function setup()
    {

        $this->addParameter((new Parameter("auto-update", Parameter::TYPE_BOOLEAN))->setDefaultValue(null));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
    	if(!$sender instanceof Player) return false;
        if (isset($args[0])) {
            $val = $args[0];
            $fsender = Members::get($sender);
            if ($val) {
                $fsender->setMapAutoUpdating(true);
                $fsender->sendMessage(Localizer::translatable("map-auto-update-enabled"));
            } else {
                if ($fsender->isMapAutoUpdating()) {
                    $fsender->setMapAutoUpdating(false);
                    $sender->sendMessage(Localizer::translatable("map-auto-update-disabled"));
                }
            }
            return true;
        }

        /** @var Player $sender $map */
        $map = Collections::getMap($sender, Collections::MAP_WIDTH, Collections::MAP_HEIGHT, $sender->getYaw());
        foreach ($map as $line) {
            $sender->sendMessage($line);
        }
        return true;
    }

}