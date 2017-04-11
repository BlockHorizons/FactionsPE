<?php
/*
 *   Dominate: Advanced command library for PocketMine-MP
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
namespace dominate\requirement;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use localizer\Translatable;

class SimpleRequirement extends Requirement {

	const OP 		= 0x1;
	const NOT_OP 	= 0x2;
	const ALIVE 	= 0x3;
	const DEAD 		= 0x4;
	const PLAYER 	= 0x5;
	const CONSOLE 	= 0x6;

	public static $ERROR_MESSAGES = [
		self::OP 		=> "requirement.op-error",
		self::NOT_OP 	=> "requirement.not-op-error",
		self::ALIVE 	=> "requirement.alive-error",
		self::DEAD 		=> "requirement.dead-error",
		self::PLAYER 	=> "requirement.player-error"
	];

	/** @var int|string */
	protected $type;

	public function __construct(int $type = null) {
		$this->type = $type ?? -1;
	}

	public function createErrorMessage(CommandSender $sender) : Translatable {
		return new Translatable(self::$ERROR_MESSAGES[$this->type], [($sender instanceof Player) ? $sender->getDisplayName() : $sender->getName()]);
	}

	public function hasMet(CommandSender $sender, $silent = false) : bool {
		$r = call_user_func(function() use ($sender) {
			switch ($this->type) {
				case self::OP:
					return ($sender instanceof Player) ? $sender->isOp() : true;
				case self::NOT_OP:
					return ($sender instanceof Player) ? !$sender->isOp() : true;
				case self::ALIVE:
					return ($sender instanceof Player) ? $sender->isAlive() : false;
				case self::DEAD:
					return ($sender instanceof Player) ? !$sender->isAlive() : false;
				case self::PLAYER:
					return ($sender instanceof Player) ? true : false;
				case self::CONSOLE:
					return ($sender instanceof ConsoleCommandSender) ? true : false;
				default:
					return false;
			}
		});
		if(!$r && !$silent) {
			$sender->sendMessage($this->createErrorMessage($sender));
		}
		return $r;
	}

}