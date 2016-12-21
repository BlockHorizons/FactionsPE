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

namespace factions\command\requirement;

use pocketmine\command\CommandSender;
use dominate\requirement\SimpleRequirement;
use factions\manager\Members;

class FactionRequirement extends SimpleRequirement {

	const IN_FACTION = 7;
	const OUT_FACTION = 8;

	public static function onClassLoaded() {
		SimpleRequirement::$ERROR_MESSAGES[self::IN_FACTION] = "requirement.in-faction-error";
		SimpleRequirement::$ERROR_MESSAGES[self::OUT_FACTION] = "requirement.in-faction-error";
	}

	public function hasMet(CommandSender $sender, $silent = false) : bool {
		$r = call_user_func(function() use ($sender) {
			switch ($this->type) {
				case self::IN_FACTION:
					return (Members::get($sender))->hasFaction();
				case self::OUT_FACTION:
					return (Members::get($sender))->hasFaction();
			}
			return false;
		});
		if(!$r && !$silent) {
			$sender->sendMessage($this->createErrorMessage($sender));
		}
		return $r;
	}

}