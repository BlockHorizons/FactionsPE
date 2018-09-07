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
use factions\utils\Pager;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Help extends Command {

	public function setup() {
		$this->addParameter((new Parameter("page|command"))->setDefaultValue(1));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		if (count($args) === 0) {
			$command    = "";
			$pageNumber = 1;
		} elseif (is_numeric($args[count($args) - 1])) {
			$pageNumber = (int) array_pop($args);
			if ($pageNumber <= 0) {
				$pageNumber = 1;
			}
			$command = implode(" ", $args);
		} else {
			$command    = implode(" ", $args);
			$pageNumber = 1;
		}
		if ($sender instanceof Player === false) {
			$pageHeight = PHP_INT_MAX;
		} else {
			$pageHeight = 5;
		}

		$main = $sender->getServer()->getCommandMap()->getCommand("faction");
		if ($command === "") {

			$commands = [];
			foreach ($main->getChilds() as $command) {
				if ($command->testPermissionSilent($sender)) {
					$commands[$command->getName()] = $command;
				}
			}

			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$pager = new Pager("help-header", $pageNumber, $pageHeight, $commands, $sender, function (Command $cmd, int $index, CommandSender $sender) {
				return Localizer::trans('help-line', ["color" => "<green>", "name" => $cmd->getName(), "desc" => $cmd->getDescription()]);
			});
			$pager->stringify();

			$pager->sendTitle($sender);

			foreach ($pager->getOutput() as $l) {
				$sender->sendMessage($l);
			}

			return true;
		} else {
			$w   = explode(" ", $command);
			$cmd = null;
			$i   = 0;
			do {
				if ($i === 0) {
					$cmd = $main->getChild($w[$i]);
				} else {
					$cmd = $cmd->getChild($w[$i]);
				}

				$i++;
			} while ($cmd instanceof Command and $cmd->isParent() and isset($w[$i]));
			if ($cmd instanceof Command) {
				if ($cmd->testPermissionSilent($sender)) {
					$message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: " . $cmd->getName() . TextFormat::YELLOW . " ---------\n";
					$message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $cmd->getDescription() . "\n";
					$message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $cmd->getUsage() . "\n";
					$sender->sendMessage($message);
					return true;
				}
			} else {
				$sender->sendMessage(Localizer::translatable("no-help-for-command", [
					"command" => $command,
				]));
			}
			return true;
		}
	}

}