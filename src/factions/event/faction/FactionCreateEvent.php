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

namespace factions\event\faction;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;

use factions\entity\Faction;
use factions\entity\IMember;

class FactionCreateEvent extends FactionEvent implements Cancellable {

	public static $handlerList = null;
	public static $eventPool = [];
	public static $nextEvent = 0;

	/** @var string */
	protected $name, $factionId;

	/** @var IMember */
	protected $creator;

	public function __construct(IMember $creator, string $factionId, string $name) {
		$this->name = $name;
		$this->factionId = $factionId;
		$this->creator = $creator;
	}

	public function getCreator() {
		return $this->creator;
	}

	public function getName() {
		return $this->name;
	}

	public function setName(string $name) {
		if(!empty($errors = Faction::validateName($name))) {
			$e = "Name '$name' is invalid. Found ".count($errors)." while validating:".PHP_EOL;
			foreach ($errors as $i => $error) {
				$e .= ($i + 1).": ".$error.($i !== count($errors) - 1 ? PHP_EOL : "");
			}
			throw new \InvalidArgumentException($e);
		}
		$this->name = $name;
	}

	public function getFactionId() {
		return $this->factionId;
	}


}