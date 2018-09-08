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
use factions\manager\Factions;
use factions\manager\Members;
use factions\utils\Pager;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class ListCmd extends Command {

	public function setup() {
		$this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1)); // /f list [page=1]
	}

	public function perform(CommandSender $sender, $label, array $args) {
		$page     = $this->getArgument(0);
		$factions = Factions::getAll();

		$pager = new Pager("list-header", $page, $sender instanceof ConsoleCommandSender ? 15 : 5, $factions, $sender, function ($faction, $i, $sender) {
			if ($faction->isNone()) {
				return Localizer::trans("list-wilderness", [count(Members::getFactionless()) - 1]); // minus one, because of CONSOLE object
				# <i>Factionless<i> %d online
			} else {
				if ($faction->isSpecial() && !Members::get($sender)->isOverriding()) {
					return null;
				}

				return Localizer::trans("list-info",
					[
						$faction->getName(),
						count($faction->getOnlineMembers()),
						count($faction->getMembers()),
						$faction->getLandCount(),
						($p = $faction->getPower(true)) === PHP_INT_MAX ? Localizer::trans("infinity") : $p,
						($p = $faction->getPowerMax()) === PHP_INT_MAX ? Localizer::trans("infinity") : $p,
					]
				) . ($sender->isOp() ? ($faction->hasLandInflation() ? TextFormat::RED . " <LAND INFLATION>" : "") : "");
			}

		});
		$pager->stringify();
		$pager->sendTitle($sender);

		foreach ($pager->getOutput() as $line) {
			$sender->sendMessage($line);
		}

		return;
	}

}