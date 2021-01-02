<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.5.4
 * Time: 23:40
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\command\parameter\FactionParameter;
use pocketmine\command\CommandSender;

class RelationSetQuick extends Command {

	/** @var RelationSet */
	private $real;

	public function setup() {
		$this->addParameter(new FactionParameter("faction|member", true));
		$this->real = $this->getParent()->getChild("relation")->getChild("set");
	}

	public function perform(CommandSender $sender, $label, array $args) {
		return $this->real->execute($sender, $label, [$args[0], $this->getName()]);
	}

}