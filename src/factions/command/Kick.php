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

use factions\command\parameter\MemberParameter;
use factions\command\requirement\FactionPermission;
use factions\permission\Permission;
use factions\manager\Permissions;
use factions\manager\Members;
use factions\relation\Relation;
use factions\event\member\MembershipChangeEvent;
use factions\utils\Gameplay;
use factions\FactionsPE;

use pocketmine\command\CommandSender;

use localizer\Localizer;

class Kick extends Command {

	public function setup() {
		$this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));
		$this->addRequirement(new FactionPermission(Permissions::getById(Permission::KICK)));
	}

	public function execute(CommandSender $sender, $label, array $args) {
		if(!parent::execute($sender, $label, $args)) return false;

		$target = $this->getArgument(0);
		$msender = Members::get($sender);
		$overriding = $msender->isOverriding();

		// Validate
		if($target === $msender) {
			$sender->sendMessage(Localizer::translatable("cant-kick-yourself", [
				"command" => $this->getParent()->getChild("leave")->getUsage()
				]));
			return true;
		}
		if($target->isLeader() && !$overriding) {
			$sender->sendMessage(Localizer::translatable("cant-kick-leader", [
				"leader" => $target->getDisplayName()
				]));
			return true;
		}
		if(Relation::isHigherThan($target->getRole(), $msender->getRole()) && !$overriding) {
			$sender->sendMessage(Localizer::translatable("cant-kick-higher-rank", [
				"who" => $target->getDisplayName()
				]));
			return true;
		}
		if($target->getRole() === $msender->getRole() && !$overriding) {
			$sender->sendMessage(Localizer::translatable("cant-kick-same-rank", [
				"who" => $target->getDisplayName(),
				]));
			return true;
		}
		if(Gameplay::get("can-leave-with-negative-power", false) && $target->getPower() < 0 && !$overriding) {
			$sender->sendMessage(Localizer::translatable("cant-kick-player-with-negative-power", [
				"who" => $target->getDisplayName()
				]));
			return true;
		}
		
		$faction = $target->getFaction();
		if(!$faction->isPermitted($faction->getRelationTo($msender), Permissions::getById(Permission::KICK))) return;		

		$event = new MembershipChangeEvent($target, $faction, MembershipChangeEvent::REASON_KICK);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if($event->isCancelled()) return;

		$faction->sendMessage(Localizer::translatable("member-kicked-inform-faction", [
			"by" => $sender->getDisplayName(),
			"who" => $target->getDisplayName(),
			]));
		$target->sendMessage(Localizer::translatable("member-kicked-inform-target", [
			"by" => $sender->getDisplayName(),
			"faction" => $faction->getName()
			]));
		if($target->getFaction() !== $msender->getFaction()) {
			$target->sendMessage(Localizer::translatable("member-kicked-from-other-faction", [
				"faction" => $faction->getName(),
				"who" => $target->getDisplayName()
				]));
		}

		if(Gameplay::get("log.member-kick", true)) {
			FactionsPE::get()->getLogger()->info(Localizer::trans("log.member-kick", [
				"by" => $sender->getDisplayName(),
				"who" => $target->getDisplayName(),
				"faction" => $faction->getName()
				]));
		}

		// If sender managed to kick leader from his faction then lets promote new one
		if($target->isLeader()) {
			$faction->promoteNewLeader();
		}
		$faction->setInvited($target, false);
		$faction->removeMember($target);
		$target->resetFactionData();
	}

}