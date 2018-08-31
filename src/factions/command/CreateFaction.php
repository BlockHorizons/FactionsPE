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
use factions\command\requirement\FactionRequirement;
use factions\entity\Faction;
use factions\event\faction\FactionCreateEvent;
use factions\event\member\MembershipChangeEvent;
use factions\FactionsPE;
use factions\manager\Factions;
use factions\manager\Members;
use factions\utils\Gameplay;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class CreateFaction extends Command {

	public function setup() {
		$this->addParameter(new Parameter("name", Parameter::TYPE_STRING));
		$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
		$this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
	}

	public function perform(CommandSender $sender, $label, array $args): bool{
		$need = 0;
		if (FactionsPE::get()->economyEnabled()) {
			if ($sender instanceof Player) {
				if (($has = FactionsPE::get()->getEconomy()->balance($sender)) < ($need = Gameplay::get('price.faction-creation', 0))) {
					$sender->sendMessage(Localizer::translatable('faction-create-insufficient-fund', [
						"has"  => $has,
						"need" => $need,
					]));
					return true;
				}
			}
		}

		$name = $this->getArgument(0);

		$errors = Faction::validateName($name);
		if (($c = count($errors)) > 0) {
			$sender->sendMessage(Localizer::translatable('invalid-faction-name', [
				"name"  => $name,
				"count" => $c,
			]));
			foreach ($errors as $n => $error) {
				$sender->sendMessage(Localizer::translatable('invalid-faction-name-error', [
					"error" => $error,
					"n"     => $n + 1,
				]));
			}
			return true;
		}

		$fid     = Faction::createId();
		$creator = Members::get($sender);

		$event = new FactionCreateEvent($creator, $fid, $name);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) {
			return false;
		}

		$faction = new Faction($fid, [
			"name"    => $name,
			"creator" => $creator,
		]);
		Factions::attach($faction);
		$creator->updateFaction();

		$event = new MembershipChangeEvent($creator, $faction, MembershipChangeEvent::REASON_CREATE);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		// Ignore cancellation

		if (FactionsPE::get()->economyEnabled()) {
			if ($sender instanceof Player) {
				FactionsPE::get()->getEconomy()->takeMoney($sender, $need);
			}
		}

		$sender->sendMessage(Localizer::translatable('faction-created', compact("name")));
		if (Gameplay::get('log.faction-creation', true)) {
			FactionsPE::get()->getLogger()->info(Localizer::trans('log.member-created-faction', [
				$creator->getName(),
				$faction->getName(),
			]));
		}
		try {
			$faction->save();
		} catch (\Exception $e) {
			// Ignore :D
		}

		return true;
	}

	public function createForm(Player $player) {
		$fapi = $this->getPlugin()->getFormAPI();
		$form = $fapi->createCustomForm(function (Player $player, array $data) {
			$result = $data[1];
			if ($result !== null) {
				$this->execute($player, "", [$result]);
			}
		});

		$form->setTitle(Localizer::trans("create-faction-form-title"));
		$form->addLabel(Localizer::trans("create-faction-form-content"));
		$form->addInput(Localizer::trans(""));
		$form->sendToPlayer($player);
	}

}
