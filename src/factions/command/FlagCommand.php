<?php
namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use factions\manager\Permissions;

class FlagCommand extends Command {

	public function setup() {
		$plugin = $this->getPlugin();
		//$this->addChild(new FlagSet($plugin, "set", "Set flag value", Permissions::FLAG_SET));
		$this->addChild(new FlagShow($plugin, "show", "See faction flag settings", Permissions::FLAG_SHOW));
		$this->addChild(new FlagList($plugin, "list", "List all registered flags", Permissions::FLAG_LIST));

		$this->addParameter(new Parameter("set|show|list"));
	}

}