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
use factions\command\parameter\FactionParameter;
use factions\entity\Faction;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\utils\Gameplay;
use factions\relation\Relation;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Info extends Command
{

    public function setup()
    {
        // Parameters
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
	    // Args
        /** @var Faction $faction */
        $faction = $this->getArgument(0);
        $member = Members::get($sender);

        // Collect data
        $id = $faction->getId();
        $description = $faction->getDescription();
        $age = $faction->getAge();
        $flags = $faction->getFlags();
        $power = [
            "Land" => $faction->getLandCount(),
            "Power" => $faction->getPower(), 
            "Maxpower" => $faction->getPowerMax()
            ];
        $f = "";
        foreach ($flags as $flag => $value) {
            $f .= ($value ? "<green>" : "<red>")."$flag <yellow>| ";
        }
        $flags = rtrim($f, "| ");

        $relations = [
            Relation::ALLY => $faction->getFactionsWhereRelation(Relation::ALLY),
            Relation::TRUCE => $faction->getFactionsWhereRelation(Relation::TRUCE),
            Relation::ENEMY => $faction->getFactionsWhereRelation(Relation::ENEMY)
        ];
        $title = Text::titleize("Faction ".$member->getColorTo($faction).$faction->getName());

        // Format and send
        $member->sendMessage($title);
        $member->sendMessage(Text::parse("<gold>ID: <yellow>".$id));
        $member->sendMessage(Text::parse("<gold>Description: <yellow>".$description));
        $member->sendMessage(Text::parse("<gold>Created: <purple>".Text::ago($age)));
        $member->sendMessage(Text::parse("<gold>Flags: ".$flags));
        $member->sendMessage(Text::parse("<gold>".implode("/", array_keys($power)).": <yellow>".implode("/", array_values($power))));
        foreach ($relations as $rel => $factions) {
            $member->sendMessage(Text::parse("<gold>Relation ".Relation::getColor($rel).ucfirst($rel)."<gold>(".count($factions)."):"));
            if(empty($factions)) {
                $member->sendMessage(Text::parse("<gray>none"));
            } else {
                $member->sendMessage(Text::parse(implode(" ", array_map(function($f){return $f->getName();}, $factions))));
            }
        }

        return true;
    }

}
