<?php
namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use factions\flag\Flag;
use factions\manager\Factions;
use factions\manager\Flags;
use factions\manager\Members;
use factions\utils\Pager;
use pocketmine\command\CommandSender;

class FlagList extends Command {

	public function setup() {
		$this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
	}

	// -------------------------------------------- //
	// OVERRIDE
	// -------------------------------------------- //

	public function perform(CommandSender $sender, $label, array $args) {
		// Parameter
		$page   = $this->getArgument(0);
		$member = Members::get($sender);

		// Pager create
		$pager = new Pager("flag-list-title", $page, 5, array_filter(Flags::getAll(), function ($flag) {
			return $flag->isVisible();
		}), $sender, function (Flag $flag, int $index,  ? CommandSender $sender = null) {
			return $flag->getStateDesc(false, false, true, true, true, false);
		});
		$pager->stringify();

		$pager->sendTitle($sender);
		foreach ($pager->getOutput() as $line) {
			$member->sendMessage($line);
		}
		return true;
	}

}
