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

namespace factions\command;

use dominate\Command;

use factions\relation\Relation;
use factions\command\parameter\MemberParameter;

class Rank extends Command {

	const RANK_REQUIRED = Relation::OFFICER;

	public function setup() {
		$this->addParameter(new MemberParameter("player"));
		$this->addParameter((new RankParameter("action"))->setDefaultValue(null)->setPermission(Permissions::RANK_ACTION));
		$this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		$this->sender = $sender;

		$member = $this->getArgument(0);
		$msender = Members::get($sender);
		$faction = $this->getArgument(2);

		// Show him the rank if no action is required
		if(!$this->getParameter("action")->isset()) {
			if(!$sender->hasPermission(Permissions::RANK_SHOW)) {
				return "no-perm-to-see-member-rank";
			}
			# Show rank
			return true;
		}		
		$action = strtolower($this->getArgument(1));

		if(Relation::isRankValid($action)) {
			$targetRank = $action;
		} else {
			if(RelationParameter::isPromotion($action)) {
				$targetRank = Relation::getNext($member->getRole());
			} elseif(RelationParameter::isDemotion($action)) {
				$targetRank = Relation::getPrevious($member->getRole());
			}
			if(!Relation::isRankValid($action)) {
				return "cant-demote-premote-rank-border";
			}
		}


		// Ensure allowed
		if(!$msender->isOverriding()) {
			if($faction->isNone()) {
				return "wilderness-doesnt-use-ranks";
			}
			if($faction !== $member->getFaction()) {
				return ["target-must-be-in-same-faction", [$member->getDisplayName()]];
			}
			if($msender === $member) {
				return "cant-change-self-rank";
			}
			if($factionChange) {
				return ["cant-change-faction", [$member->getDisplayName()]];
			}
			if(Relation::isLessThan($msender->getRole(), RankParameter::REQUIRED_RANK)) {
				return ["not-enough-rank-power", [RankParameter::REQUIRED_RANK]];
			}
			
		}

	}

}