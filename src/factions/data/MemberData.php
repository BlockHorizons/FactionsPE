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

namespace factions\data;

use factions\FactionsPE;
use factions\relation\Relation;
use factions\manager\Members;

class MemberData extends Data {

	/** @var string */
	protected $name;

    /**
     * When the player first time played in faction
     * @var int
     */
    protected $firstPlayed;

    /** @var int */
    protected $lastPlayed;

    /** @var int */
    protected $power = 0;
    protected $powerBoost;

    /** @var int */
    protected $role;

    /** @var string */
    protected $title;

    /**
     * @var Player|null
     */
    protected $player;

    /** @var boolean */
    protected $overriding = false;

    public function __construct(array $source) {
    	$this->firstPlayed = $source["firstPlayed"] ?? 0;
    	$this->lastPlayed = $source["lastPlayed"] ?? time();
    	$this->power = $source["power"] ?? 0;
    	$this->role = $source["role"] ?? Relation::RECRUIT;
    	$this->title = $source["title"] ?? null;
    	$this->player = FactionsPE::get()->getServer()->getPlayer($source["player"] ?? "");
    	if($this->player) {
    		$this->name = $this->player->getName();
    	} else {
    		$this->name = $source["name"] ?? ($source["player"] ?? "");
    	}
    }

    public function __toArray() {
    	return [
    		"firstPlayed" => $this->firstPlayed,
    		"lastPlayed" => $this->lastPlayed,
    		"power" => $this->power,
    		"role" => $this->role,
    		"title" => $this->title,
    		"player" => $this->name
    	];
    }

    public function save() {
    	FactionsPE::get()->getDataProvider()->saveMember(Members::get($this->name));
    }

}