<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\manager\Members;
use fpe\localizer\Localizer;
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