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

	const ONLINE_MEMBER 	= "member";
	const OFFLINE_MEMBER 	= "offline-member";
	const CONSOLE_MEMBER 	= "console-member";
	const ANY_MEMBER		= "any-member";

	public static function onClassLoaded() {
		Parameter::$ERROR_MESSAGES[self::ONLINE_MEMBER] 	= "parameter.type-member-error";
		Parameter::$ERROR_MESSAGES[self::OFFLINE_MEMBER] 	= "parameter.type-member-error";
		Parameter::$ERROR_MESSAGES[self::CONSOLE_MEMBER] 	= "parameter.type-console-member";
		Parameter::$ERROR_MESSAGES[self::ANY_MEMBER]		= "parameter.type-any-member";
	}

	/**
	 * @param string $input
	 * @return mixed
	 */
	public function read(string $input, CommandSender $sender = null) {
		$silent = $sender ? false : true;
		$member = Members::get($input, false);
		if($member) {
			if($this->isValid($member, $sender)) {
				if(!$silent) {
					$sender->sendMessage($this->createErrorMessage($sender, $input));
				}
				return null;
			}
		}
		return $member;
	}

	public function isValid($value, CommandSender $sender = null) : bool {
		switch ($this->type) {
				case self::ONLINE_MEMBER:
					if($member instanceof Member && $member->isOnline()) {
						return true;
					} else {
						return false;
					}
				case self::OFFLINE_MEMBER:
					if($member instanceof OfflineMember) {
						return true;
					} else {
						return false;
					}
				case self::CONSOLE_MEMBER:
					return $member instanceof FConsole;
				case self::ANY_MEMBER:
					return $member instanceof IMember;
				default:
					return false;
			}
	}

}