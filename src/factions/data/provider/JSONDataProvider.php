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
use factions\entity\Member;
use factions\entity\Faction;
use factions\manager\Plots;
use factions\utils\Text;

class JSONDataProvider extends DataProvider {
	use MemberFilePath, FactionFilePath;

	protected function prepare() {
		@mkdir($this->getMain()->getDataFolder()."factions");
		@mkdir($this->getMain()->getDataFolder()."members");
		@touch($this->getMain()->getDataFolder()."flags.json");
		@touch($this->getMain()->getDataFolder()."permissions.json");
	}

	public function saveMember(MemberData $member) {
		$f = $this->getMemberFilePath($member, ".json");
		@mkdir(dirname($f));
		file_put_contents($f, self::json_encode($member->__toArray()));
	}

	public function saveFaction(FactionData $faction) {
		$f = $this->getFactionFilePath($faction, ".json");
		file_put_contents($f, self::json_encode($faction->__toArray()));
	}

	public function loadMember(string $name) {
		if(file_exists($f = $this->getMemberFilePath($name, ".json"))) {
			return new MemberData(json_decode(file_get_contents($f), true));
		}
		return null;
	}

	public function loadFaction(string $id) {
		if(file_exists($f = $this->getFactionFilePath($id, ".json"))) {
			return new Faction($id, json_decode(file_get_contents($f), true));
		}
		return null;
	}

	public function savePlots(array $plots) {
		file_put_contents($this->getMain()->getDataFolder()."plots.json", self::json_encode($plots));
	}

	public function loadPlots() {
		if(!file_exists(($f = $this->getMain()->getDataFolder()."plots.json"))) return;
		Plots::setPlots(json_decode(file_get_contents($f), true));
	}

	/**
	* @param string
	*/
	public function deleteMember(string $identifier) {
		if(file_exists($f = $this->getMemberFilePath($identifier, ".json"))) {
			unlink($f);
		}
	}

	/**
	* @param string
	*/
	public function deleteFaction(string $identifier) {
		if(file_exists($f = $this->getFactionFilePath($identifier,  ".json"))) {
			unlink($f);
		}
	}

	public function loadFactions() {
		$special = [Faction::NONE, Faction::SAFEZONE, Faction::WARZONE];
		$files = glob($this->getMain()->getDataFolder()."factions/*.json");
		$files = array_map(function($el){
			return substr($el, strpos($el, "factions/") + 9, -4);
		}, $files);
		foreach(DataProvider::order($files) as $faction) {
			$this->loadFaction($faction);
		}
	}

	public function saveFlags(array $flags) {
		$save = [];
		foreach ($flags as $flag) {
			$save[$flag->getId()] = $flag->__toArray();
		}
		file_put_contents($this->getFlagsFile(), self::json_encode($save));
	}

	public function loadFlags() {
		if(file_exists($this->getFlagsFile())) {
			$data = file_get_contents($this->getFlagsFile());
			if(empty($data)) return;
			$flags = json_decode($data, true);
			foreach ($flags as $id => $flag) {
				$this->loadFlag($id, $flag);
			}
		}
	}

	public function loadPermissions() {
		if(file_exists($this->getPermsFile())) {
			$data = file_get_contents($this->getPermsFile());
			if(empty($data)) return;
			$perms = json_decode($data, true);
			foreach ($perms as $id => $perm) {
				$this->loadPermission($id, $perm);
			}
		}
	}

  	public function savePermissions(array $permissions) {
  		$s = [];
  		foreach ($permissions as $perm) {
  			$s[$perm->getId()] = $perm->__toArray();
  		}
  		file_put_contents($this->getPermsFile(), self::json_encode($s));
  	}

	public function getFlagsFile() : string {
		return $this->getMain()->getDataFolder()."flags.json";
	}

	public function getPermsFile() : string {
		return $this->getMain()->getDataFolder()."permissions.json";
	}

	public function close() {

	}

  public static function json_encode(array $data) {
    return Text::prettyPrint(json_encode($data));
  }

	public function getName() : string {
		return "JSON";
	}

}
