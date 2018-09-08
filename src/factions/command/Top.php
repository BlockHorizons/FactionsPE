<?php
namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use factions\flag\Flag;
use factions\manager\Factions;
use factions\utils\Pager;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class Top extends Command {

	public function setup() {
		$this->addParameter((new Parameter("power|online"))->setDefaultValue("power"));
		$this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		$height = 5;
		$sortBy = $this->getArgument(0);
		if ($sender instanceof ConsoleCommandSender) {
			$height = 20;
		}

		$factions = Factions::getAll();
		$order    = [];
		$keys     = [];
		$book     = [];

		if ($sortBy === "power") {
			foreach ($factions as $faction) {
				if ($faction->getFlag(Flag::INFINITY_POWER) || $faction->isNone() or $faction->isSpecial()) {
					continue;
				}
				$order[$faction->getName()] = $faction->getPower();
				$keys[]                     = $faction->getName();
			}
			arsort($order);
			foreach ($order as $key => $power) {
				$book[$key] = [$key, $power];
			}
		} elseif ($sortBy === "online") {
			foreach ($factions as $faction) {
				if ($faction->getTimeJoined() === null) {
					continue;
				}

				$order[$faction->getName()] = $faction->getOnlineTime();
				$keys[]                     = $faction->getName();
			}
			arsort($order);
			foreach ($order as $key => $time) {
				$book[$key] = [$key, $time];
			}
		} else {
			return ["unknown-sort-by", ['sort' => $sortBy]];
		}

		if (empty($book)) {
			$sender->sendMessage(Localizer::translatable("top-empty"));
			return true;
		}
		$page = $this->getArgument(0);

		$pager = new Pager("top-header", $page, $height, $book, $sender, function (array $d, int $i, CommandSender $sender) use ($height, $page, $sortBy) {
			$value = $sortBy === "power" ? $d[1] : Text::time_elapsed($d[1], true);
			return Localizer::trans('top-line', [($page - 1) * $height + $i + 1, $d[0], $value]);
		});
		$pager->stringify();
		$pager->sendTitle($sender, ["sort-by" => $sortBy]);

		foreach ($pager->getOutput() as $l) {
			$sender->sendMessage($l);
		}

		return true;
	}
}