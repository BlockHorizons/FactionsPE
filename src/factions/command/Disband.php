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

use factions\command\parameter\FactionParameter;
use factions\command\requirement\FactionRequirement;
use factions\command\requirement\FactionPermission;
use factions\manager\Members;
use factions\event\faction\FactionDisbandEvent;
use factions\manager\Permissions;
use factions\permission\Permission;
use factions\flag\Flag;
use factions\entity\Faction;

use pocketmine\command\CommandSender;

class Disband extends Command {

	public function setup() {
		// Parameters
		$this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		// Args
		$faction = $this->getArgument(0);
		$member = Members::get($sender);
		
		// MPerm
		if (!($perm = Permissions::getById(Permission::DISBAND))->has($member, $faction)) {
			return ["faction-permission-error", ["perm_desc" => $perm->getDescription()]];
		}

		// Verify
		if ($faction->getFlag(Flag::PERMANENT)) {
			return "cant-disband-permanent";
		}

		// Event
		$event = new FactionDisbandEvent($member, $faction);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) return;

		// Merged Apply and Inform
		$faction->disband(Faction::DISBAND_REASON_COMMAND);

		// Inform
		foreach ($faction->getOnlineMembers() as $member) {
			$member->sendMessage(Localizer::translatable("faction-disbanded-inform-member", [$member->getName()]));
		}
		
		if ($member->getFaction() != $faction) {
			return ["you-disbanded", [$faction->getName()]];
		}
		
		// Log
		if (Gameplay::get("log.faction-disband", true)) {
			FactionsPE::get()->getLogger()->notice(Localizer::translatable("log.faction-disband-by-command", $faction->getName(), $faction->getId(), $sender->getDisplayName()));
		}		
		
		// Apply
		$faction->detach();
		return true;
	}
	
}
