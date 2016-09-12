<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\subcommand\childs\PermListChild;
use factions\command\subcommand\childs\PermSetChild;
use factions\command\subcommand\childs\PermShowChild;
use factions\FactionsPE;

class PermSubCommand extends Command {
	
	public function __construct(FactionsPE $plugin) {
		parent::__construct($plugin, "perm", "Manage faction permissions", FactionsPE::PERM, ["permission"]);
		$this->addParameter(new Parameter("list|show|set", new TypeString(), false));
	
		$this->addChild(new PermListChild($plugin));
		$this->addChild(new PermShowChild($plugin));
		$this->addChild(new PermSetChild($plugin));

		$this->addRequirement(new ReqBePlayer());
	}

}