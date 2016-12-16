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
use factions\data\FactionData;

# Our best is yet to come (Howling at the moon)

class YAMLDataProvider extends DataProvider {
	use MemberFilePath, FactionFilePath;

	protected function prepare() {
		// Nothing to do here :)
	}

	public function saveMember(MemberData $member) {
		$f = $this->getMemberFilePath($member, ".yml");
		@mkdir(dirname($f));
		file_put_contents($f, yaml_emit($member->__toArray()));
	}

	public function saveFaction(FactionData $faction) {
		$f = $this->getFactionFilePath($faction, ".yml");
		@mkdir(dirname($f));
		file_put_contents($f, yaml_emit($faction->__toArray()));
	}

	public function loadMember(string $name) {
		if(file_exists($f = $this->getMemberFilePath($name, ".yml"))) {
			return new MemberData(yaml_parse(file_get_contents($f)));
		}
		return null;
	}

	public function loadFaction(string $id) {
		var_dump($id);
		if(file_exists($f = $this->getFactionFilePath($id, ".yml"))) {
			return new FactionData(yaml_parse(file_get_contents($f)));
		} else {
			var_dump($f);
		}
		return null;
	}

	/**
	* @param string
	*/
	public function deleteMember(string $identifier) {
		if(file_exists($f = $this->getMemberFilePath($identifier, ".yml"))) {
			unlink($f);
		}
	}

	/**
	* @param string
	*/
	public function deleteFaction(string $identifier) {
		if(file_exists($f = $this->getFactionFilePath($identifier,  ".yml"))) {
			unlink($f);
		}
	}

	public function loadFactions() {
		## TODO
	}

	public function close() {

	}

	public function getName() : string {
		return "YAML";
	}

}
