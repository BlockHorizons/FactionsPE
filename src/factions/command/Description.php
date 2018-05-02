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
use pocketmine\command\CommandSender;
use localizer\Localizer;
use dominate\parameter\Parmater;
use factions\command\requirement\FactionRequirement;
use factions\manager\Members;

class Description extends Command {

	public function setup() {
		$this->addParameter(new Parameter("...description", Parameter::TYPE_STRING));
		//$this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		if(!($m = Members::get($sender))->isLeader() && !$m->isOverriding()) {
			return ["requirement.faction-permission-error", ["perm_desc" => "set description"]]; # Not fully translatable TODO
		}

		$description = implode(" ", $args);

		if(strlen($description) > 62) {
			return "description-too-long";
		}

		$faction = $m->getFaction();

		$faction->setDescription($description);

		$faction->sendMessage(Localizer::translatable("description-updated", [
			"player" => $m->getDisplayName()
			]));
		$faction->sendMessage(Localizer::translatable("new-description", [
			"description" => $description
			]));
		return true;
	}

}