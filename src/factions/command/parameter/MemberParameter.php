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

use factions\entity\FConsole;
use factions\entity\IMember;
use factions\entity\OfflineMember;
use factions\entity\Member;
use factions\manager\Members;

use dominate\parameter\Parameter;

class MemberParameter extends Parameter {

	const ONLINE_MEMBER 	= 0;
	const OFFLINE_MEMBER 	= 1;
	const CONSOLE_MEMBER 	= 2;
	const ANY_MEMBER		= 3;

	public function setup() {
		$this->ERROR_MESSAGES = [
			self::ONLINE_MEMBER 	=> "type-member",
			self::OFFLINE_MEMBER 	=> "type-member",
			self::CONSOLE_MEMBER 	=> "type-console-member",
			self::ANY_MEMBER		=> "type-any-member",
		];
	}

	/**
	 * @param string $input
	 * @return mixed
	 */
	public function read(string $input, CommandSender $sender = null) {
		if(($input === "me" || $input === "self") && $sender) {
			$member = Members::get($sender, true);
		} else {
			$member = Members::get($input, false);
		}
		return $member;
	}

	public function isValid($value, CommandSender $sender = null) : bool {
		if($value === null) return false;
		switch ($this->type) {
			case self::ONLINE_MEMBER:
				return $value instanceof Member && $value->isOnline();
			case self::OFFLINE_MEMBER:
				return $value instanceof OfflineMember;
			case self::CONSOLE_MEMBER:
				return $value instanceof FConsole;
			case self::ANY_MEMBER:
				return $value instanceof IMember;
			default:
				return false;
		}
	}

}