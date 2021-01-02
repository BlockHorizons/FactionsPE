<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\utils\Gameplay;
use pocketmine\command\CommandSender;

class Reload extends Command
{

    public function perform(CommandSender $sender, $label, array $args)
    {
        # Reload config
        $this->getPlugin()->reloadConfig();
        # Set Gameplay data
        Gameplay::setData($this->getPlugin()->getConfig()->get('gameplay', []));

        if (Gameplay::get("log.config-reload", true)) {
            $this->getPlugin()->getLogger()->notice(Localizer::trans("log.config-reloaded", [$sender->getName()]));
        }

        # Reload language files
        Localizer::clean();
        Localizer::loadLanguages($this->getPlugin()->getDataFolder() . "languages");

        return ["config-reloaded", [$sender->getName()]];
    }

}
