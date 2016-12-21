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
namespace factions\data\provider;

use factions\data\MemberData;

trait MemberFilePath {

	/**
	 * @param MemberData|string $member
	 * @param $ext with fullstop
	 */
	public function getMemberFilePath($member, string $ext) {
		$name = strtolower(trim($member instanceof MemberData ? $member->getName() : $member));
		return $this->getMain()->getDataFolder()."members/".substr($name, 0, 1)."/".$name.$ext;
	}

}