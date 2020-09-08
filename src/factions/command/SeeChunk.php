<?php
namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;
use factions\engine\SeeChunkEngine;
use pocketmine\command\CommandSender;
use factions\manager\Members;
use localizer\Localizer;

class SeeChunk extends Command {

	function setup() {
		//$this->addAliases("sc");

		$this->addParameter(new Parameter("active", Parameter::TYPE_BOOLEAN, true));

		$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
	}

	function perform(CommandSender $sender, $label, array $args) {
		$member = Members::get($sender);
		$old = $member->isSeeingChunk();
		$target = $this->getParameterAt(0)->getValue(!$old);

		// Detect no change
		if($old === $target) {
			$member->sendMessage(Localizer::translatable('seeing-chunk-no-change'));
			return true;
		} else {
			$member->setSeeChunk($target);
		}

		/** @var SeeChunkEngine $engine */
        /** @var \pocketmine\Player $sender */
		$engine = $this->getPlugin()->getEngine("SeeChunkEngine");
		if($target) {
			$chunk = $sender->getLevel()->getChunk($sender->getX() >> 4, $sender->getZ() >> 4);
			$engine->setChunk($member, $chunk, $sender->getLevel());
		} else {
			$engine->removeChunk($member);
		}

		$member->sendMessage(Localizer::translatable('seeing-chunk-' . ($target ? 'activated' : 'deactivated')));
		return true;
	}

}