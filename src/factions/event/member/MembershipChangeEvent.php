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

namespace factions\event\member;

use pocketmine\event\Cancellable;

use factions\manager\Members;
use factions\entity\IMember;
use factions\entity\Faction;

class MembershipChangeEvent extends MemberEvent implements Cancellable {

	public static $handlerList = null;
	public static $eventPool = [];
	public static $nextEvent = 0;

	const REASON_CUSTOM		= 0x0;
	const REASON_LEAVE 		= 0x1;
	const REASON_JOIN 		= 0x2;
	const REASON_CREATE 	= 0x3;
	const REASON_KICK		= 0x4;

	/** @var Faction */
	protected $faction;

	/** @var int */
	protected $reason;

	public function __construct(IMember $member, Faction $faction, int $reason = self::REASON_CUSTOM) {
		parent::__construct($member);
		$this->faction = $faction;
		$this->reason = $reason;
	}

	public function getFaction() : Faction {
		return $this->faction;
	}

	public function getReason() : int {
		return $this->reason;
	}

	public function setCancelled($force = true) {
		if($this->reason === self::REASON_CREATE) {
			throw new \LogicException("MembershipChangeEvent(reason={self::REASON_CREATE}|REASON_CREATE) can't be cancelled");
		} else {
			parent::setCancelled($force);
		}
	}

}