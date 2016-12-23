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

use factions\FactionsPE;
use factions\manager\Permissions;
use dominate\Command;
use dominate\parameter\Parameter;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use localizer\Localizer;
use factions\command\requirement\FactionRequirement;
use factions\event\member\MembershipChangeEvent;

class CreateFaction extends Command {

	public function __construct(FactionsPE $plugin, $name, $description, $permission, $aliases) {
		parent::__construct($plugin, $name, $description, $permission, $aliases);

		$this->addParameter(new Parameter("name", Parameter::TYPE_STRING));
		$this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
	}

	public function execute(CommandSender $sender, $label, array $args) : bool {
		if(!parent::execute($sender, $label, $args)) return true;

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

		$name = $this->readArgument(0);

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

		$event = new MembershipChangeEvent($creator, $faction, MembershipChangeEvent::REASON_CREATE);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		// Ignore cancellation

		$sender->sendMessage(Localizer::translatable('faction-created', compact("name")));
		if(Gameplay::get('log.faction-creation', true)) {
			FactionsPE::get()->getLogger()->info($sender->getName()." created new faction '".$name."'");
		}

		return true;
	}

}