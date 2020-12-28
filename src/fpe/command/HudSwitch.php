<?php

namespace fpe\command;

use dominate\Command;
use dominate\requirement\SimpleRequirement;
use fpe\manager\Members;
use fpe\utils\Text;
use pocketmine\command\CommandSender;

class HudSwitch extends Command
{

    public function setup()
    {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $member = Members::get($sender);
        $member->toggleHUD();
        $sender->sendMessage(Text::parse("<i>HUD has been " . ($member->hasHUD() ? "<green>enabled" : "<b>disabled") . "<reset>"));
        return true;
    }

}
