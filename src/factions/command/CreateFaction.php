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
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;

use localizer\Localizer;

use factions\command\requirement\FactionRequirement;
use factions\event\member\MembershipChangeEvent;
use factions\event\faction\FactionCreateEvent;
use factions\manager\Permissions;
use factions\utils\Gameplay;
use factions\entity\Faction;
use factions\manager\Members;
use factions\FactionsPE;

use pocketmine\command\CommandSender;
use pocketmine\Player;

class CreateFaction extends Command {

	public function setup() {
		$this->addParameter(new Parameter("name", Parameter::TYPE_STRING));
		$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
		$this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
	}

	public function execute(CommandSender $sender, $label, array $args) : bool {
		if(!parent::execute($sender, $label, $args)) return false;

		if(FactionsPE::get()->economyEnabled()) {
			if($sender instanceof Player) {
				if(($has = FactionsPE::get()->getEconomy()->getMoney($sender)) < ($need = Gameplay::get('price.faction-creation', 0)) ) {
					$sender->sendMessage(Localizer::translatable('insufficient-fund', [
						"has" => $has,
						"need" => $need
						]));
					return true;	
				}
			}
		}

		$name = $this->getArgument(0);

		$errors = Faction::validateName($name);
		if(($c = count($errors)) > 0) {
			$sender->sendMessage(Localizer::translatable('invalid-faction-name', [
				"name" => $name,
				"count" => $c
				]));
			foreach ($errors as $n => $error) {
				$sender->sendMessage(Localizer::translatable('invalid-faction-name-error', [
					"error" => $error,
					"n" => $n + 1
					]));
			}
			return true;
		}

		$fid = Faction::createId();
		$creator = Members::get($sender);

		$event = new FactionCreateEvent($creator, $fid, $name);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if($event->isCancelled()) return;

		$faction = new Faction($fid, [
			"name" => $name,
			"creator" => $creator
			]);
		$creator->updateFaction();

		$event = new MembershipChangeEvent($creator, $faction, MembershipChangeEvent::REASON_CREATE);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		// Ignore cancellation

		if(FactionsPE::get()->economyEnabled()) {
			if($sender instanceof Player) {
				FactionsPE::get()->getEconomy()->takeMoney($sender, $need);
			}
		}

		$sender->sendMessage(Localizer::translatable('faction-created', compact("name")));
		if(Gameplay::get('log.faction-creation', true)) {
			FactionsPE::get()->getLogger()->info(Localizer::trans('log.member-created-faction', [
				$creator->getName(),
				$faction->getName()
				]));
		}

		return true;
	}

}