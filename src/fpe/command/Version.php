<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use fpe\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Version extends Command
{

    public function perform(CommandSender $sender, $label, array $args)
    {
        $sender->sendMessage(Text::titleize(Localizer::translatable("version-info-header")));
        $sender->sendMessage(Localizer::translatable("version", [
            "version" => $this->getPlugin()->getDescription()->getVersion(),
        ]));
        $sender->sendMessage(Localizer::translatable("author", [
            "author" => "Kris-Driv, Sandertv (@Sandertv)"
        ]));
        $sender->sendMessage(Localizer::translatable("organization", [
            "organization" => "BlockHorizons (https://github.com/BlockHorizons/FactionsPE)"
        ]));
        return true;
    }

}