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
use factions\manager\Members;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Chat extends Command
{

    public function perform(CommandSender $sender, $label, array $args)
    {
        $member = Members::get($sender);
        $v = $member->isFactionChatOn();

        $member->toggleFactionChat();

        if ($v && count($member->getFaction()->getOnlineMembers()) === 1) {
            $member->sendMessage(Localizer::translatable("faction-chat-disabled-due-empty"));
            return true;
        }

        $member->sendMessage(Localizer::translatable("faction-chat-" . ($v ? "disabled" : "enabled")));
        return true;
    }

}