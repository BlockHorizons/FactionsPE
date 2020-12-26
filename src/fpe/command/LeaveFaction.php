<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use fpe\command\requirement\FactionRequirement;
use fpe\manager\Members;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class LeaveFaction extends Command {

	public function setup() {
		$this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		$member  = Members::get($sender);
		$faction = $member->getFaction();
		if ($faction->leave($member)) {
			$sender->sendMessage(Localizer::translatable('you-left-faction', [
				"faction" => $faction->getName(),
			]));
		}

		return true;
	}

	public function leaveForm(Player $player) {
		if (!$this->testRequirements($player)) {
			return;
		}

		$fapi = $this->getPlugin()->getFormAPI();
		$form = $fapi->createModalForm(function (Player $player,  ? bool $result = null) {
			if ($result !== null) {
				if ($result) {
					$this->perform($player, "", []);
				}
			}
		});

		$form->setContent(Localizer::trans("form-leave-are-u-sure", [
			"faction" => Members::get($player)->getFaction()->getName(),
		]));
		$form->setButton1(Localizer::trans("button-yes"));
		$form->setButton2(Localizer::trans("button-no"));
		$form->sendToPlayer($player);
	}

}