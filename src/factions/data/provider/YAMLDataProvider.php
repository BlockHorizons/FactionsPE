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

# Our best is yet to come (Howling at the moon)

class YAMLDataProvider extends DataProvider {
	use MemberFilePath, FactionFilePath;

	protected function prepare() {
		@mkdir($this->getMain()->getDataFolder()."factions");
		@mkdir($this->getMain()->getDataFolder()."members");
	}

	public function saveMember(MemberData $member) {
		$f = $this->getMemberFilePath($member, ".yml");	
		@mkdir(dirname($f));
		file_put_contents($f, yaml_emit($member->__toArray()));
	}

	public function saveFaction(FactionData $faction) {
		$f = $this->getFactionFilePath($faction, ".yml");
		file_put_contents($f, yaml_emit($faction->__toArray()));
	}

	public function loadMember(string $name) {
		if(file_exists($f = $this->getMemberFilePath($name, ".yml"))) {
			return new MemberData(yaml_parse(file_get_contents($f)));
		}
		return null;
	}

	public function loadFaction(string $id) {
		if(file_exists($f = $this->getFactionFilePath($id, ".yml"))) {
			return new Faction($id, yaml_parse(file_get_contents($f)));
		}
		return null;
	}

	public function savePlots(array $plots) {
		file_put_contents($this->getMain()->getDataFolder()."plots.yml", yaml_emit($plots));
	}

	public function loadPlots() {
		if(!file_exists(($f = $this->getMain()->getDataFolder()."plots.yml"))) return;
		Plots::setPlots(yaml_parse_file($f));
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
		$special = [Faction::NONE, Faction::SAFEZONE, Faction::WARZONE];
		$files = glob($this->getMain()->getDataFolder()."factions/*.yml");
		$files = array_map(function($el){
			return substr($el, strpos($el, "factions/") + 9, -4);
		}, $files);
		foreach(DataProvider::order($files) as $faction) {
			$this->loadFaction($faction);
		}
	}

	public function close() {

	}

	public function getName() : string {
		return "YAML";
	}

}
