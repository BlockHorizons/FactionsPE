<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\command;

use factions\FactionsPE;
use factions\permission\Permissions;

use dominate\Command;
use dominate\argument\Argument;
use dominate\requirement\SimpleRequirement;

use pocketmine\command\CommandSender;

class FactionCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, 'faction', 'Main Faction command', Permissions::MAIN, ['fac', 'f']);

        // Registering subcommands
        //$this->addChild(new InfoSubCommand($plugin, "info", "Fetch faction info", Permissions::INFO));

        $this->addArgument(new Argument("sub-command", Argument::TYPE_BOOLEAN));
        //$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
    }

    /**
     * @param CommandSender $sender
     * @param string $label
     * @param string[] $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (!parent::execute($sender, $label, $args)) return true;
        if ($this->endPoint !== $this) return true;
        if (isset($args[0])) {
            if (!$this->getChild($args[0])) {
                $sender->sendMessage(Localizer::trans("command.generic-usage", [$args[0]]));
            }
        }
        return true;
    }
}