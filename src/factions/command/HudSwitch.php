<?php

namespace factions\command;

use dominate\Command;
use factions\manager\Members;
use factions\utils\Text;
use pocketmine\command\CommandSender;

class HudSwitch extends Command
{

    public function perform(CommandSender $sender, $label, array $args)
    {
        $member = Members::get($sender);
        $member->toggleHUD();
        $sender->sendMessage(Text::parse("<i>HUD has been " . ($member->hasHUD() ? "<green>enabled" : "<b>disabled") . "<reset>"));
        return true;
    }

}
