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

class RankParameter extends RelationParameter {

	const NAMES_PROMOTE = [
		"promote",
		"plus",
		"+",
		"up"
	];

	const NAMES_DEMOTE = [
		"demote",
		"minus",
		"-",
		"down"
	];

	const REQUIRED_RANK = Relation::OFFICER;
	
	public function setup() {
		$this->ERROR_MESSAGE = "type-rank";
		$this->type = RelationParameter::RANK;
	}

	public function read(string $input, CommandSender $sender = null) {
		return strtolower($input);
	}

	public static function isPromotion($value) : bool {
		return in_array($value, self::NAMES_PROMOTE, true);
	}

	public static function isDemotion($value) : bool {
		return in_array($value, self::NAMES_DEMOTE, true);
	}

	public function isValid($value, CommandSender $sender = null) {
		if(is_array($value, array_merge(self::NAMES_DEMOTE, self::NAMES_PROMOTE), true)) return $value;
		return parent::isValid($value, $sender);
	}

}
