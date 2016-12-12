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

use factions\entity\IMember;
use factions\entity\IFaction;

# Our best is yet to come (Howling at the moon)

class YAMLDataProvider extends DataProvider {
	use MemberFilePath, FactionFilePath;

	protected function prepare() {
		// Nothing to do here :)
	}

	public function saveMember(IMember $member) {
		$data = $member->getData();
		$f = $this->getMemberFilePath($member, ".yml");
		@mkdir(dirname($f));
		file_put_contents($f, yaml_emit($data->__toArray()));
	}

	public function saveFaction(IFaction $faction) {
		$data = $faction->getData();
		$f = $this->getFactionFilePath($faction, ".yml");
		@mkdir(dirname($f));
		file_put_contents($f, yaml_emit($data->__toArray()));
	}

	public function loadMember(string $name) {
		if(file_exists($f = $this->getMemberFilePath($name, ".yml"))) {
			return MemberData::fromArray(yaml_parse(file_get_contents($f)));
		}
		return null;
	}

	public function loadFaction(string $id) {
		if(file_exists($f = $this->getfactionFilePath($id, ".yml"))) {
			return FactionData::fromArray(yaml_parse(file_get_contents($f)));
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
