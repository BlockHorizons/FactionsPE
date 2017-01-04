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

use localizer\Localizer;

use factions\manager\Members;
use factions\utils\Text;
use factions\utils\Gameplay;
use factions\FactionsPE;

use pocketmine\command\CommandSender;

class Override extends Command {

	public function setup() {
		$this->addParameter((new Parameter("on|off", Parameter::TYPE_BOOLEAN))->setDefaultValue("toggle"));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		$msender = Members::get($sender);
		$this->parameters[0]->setDefaultValue($msender->isOverriding() ? "false" : "true");
		if(!parent::execute($sender, $label, $args)) return false;

		$msender->setOverriding($value = $this->parameters[0]->getValue());
		$sender->sendMessage(Localizer::translatable("overriding-".($value ? "enabled" : "disabled")));

		if(Gameplay::get("log.override", true)) {
			FactionsPE::get()->getLogger()->notice(Localizer::trans("log.override", [
				"player" => $msender->getName(),
				"overriding" => Text::toString($value, true)
				]));
		}

		$this->parameters[0]->setDefaultValue("toggle");
		return true;
	}

}