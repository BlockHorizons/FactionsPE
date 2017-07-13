<?php

namespace factions\command;

use dominate\Command;
use pocketmine\command\CommandSender;
use localizer\Localizer;
use factions\utils\Text;

class Version extends Command {

	public function perform(CommandSender $sender, $label, array $args) {
		$sender->sendMessage(Text::titleize(Localizer::translatable("version-info-header")));
		$sender->sendMessage(Localizer::translatable("version", [
			"version" => $this->getPlugin()->getDescription()->getVersion(),
			]));
		$sender->sendMessage(Localizer::translatable("author", [
			"author" => "Chris-Prime (@PrimusLV), Sandertv {@Sandertv}"
			]));
		$sender->sendMessage(Localizer::translatable("organization", [
			"organization" => "BlockHorizons (https://github.com/BlockHorizons/FactionsPE)"
			]));
		return true;
	}

}