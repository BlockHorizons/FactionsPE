<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use pocketmine\command\CommandSender;
use localizer\Localizer;
use fpe\utils\Text;

class Version extends Command {

	public function perform(CommandSender $sender, $label, array $args) {
		$sender->sendMessage(Text::titleize(Localizer::translatable("version-info-header")));
		$sender->sendMessage(Localizer::translatable("version", [
			"version" => $this->getPlugin()->getDescription()->getVersion(),
			]));
		$sender->sendMessage(Localizer::translatable("author", [
			"author" => "Chris-Prime (@PrimusLV (Kristaps Drivnieks)), Sandertv {@Sandertv}"
			]));
		$sender->sendMessage(Localizer::translatable("organization", [
			"organization" => "BlockHorizons (https://github.com/BlockHorizons/FactionsPE)"
			]));
		return true;
	}

}