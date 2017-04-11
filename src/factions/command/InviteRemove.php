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

use dominate\Command;
use factions\command\parameter\MemberParameter;
use factions\FactionsPE;
use factions\manager\Members;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class InviteRemove extends Command
{

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);
        $player = $this->getArgument(0);

        $faction = $msender->getFaction();
        if ($faction->isInvited($player)) {
            $faction->setInvited($player, false);
            $sender->sendMessage(Localizer::translatable("invitation-deleted", [$player->getDisplayName(), $faction->getName()]));
        } else {
            $sender->sendMessage(Localizer::translatable("not-invited", [$player->getDisplayName()]));
        }

        return true;
    }

}