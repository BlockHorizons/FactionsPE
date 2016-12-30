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

namespace factions\command\parameter;

use pocketmine\command\CommandSender;

use factions\entity\Faction;
use factions\manager\Factions;
use factions\manager\Members;

use dominate\parameter\Parameter;

class FactionParameter extends Parameter {

	public function __construct(string $name, int $type = null, int $index = null) {
		parent::__construct($name, $type, $index);
		$this->type = self::class; // This is hacky way for the error messages to show correctly
	}

	public static function onClassLoaded() {
		Parameter::$ERROR_MESSAGES[self::class] = "parameter.type-faction-error";
	}

	/**
	 * @param string $input
	 * @return Faction|null
	 */
	public function read(string $input, CommandSender $sender = null) {
		$silent = $sender ? false : true;
		if(($input === "me" || $input === "self") && $sender) {
			$faction = Members::get($sender, true)->getFaction();
		} else {
			$faction = Factions::getByName($input, false);
		}
		if(!$this->isValid($faction, $sender)) {
			if(!$silent) {
				$sender->sendMessage($this->createErrorMessage($sender, $input));
			}
			return null;
		}
		return $faction;
	}

	public function isValid($value, CommandSender $sender = null) : bool {
		return $value instanceof Faction;
	}

}