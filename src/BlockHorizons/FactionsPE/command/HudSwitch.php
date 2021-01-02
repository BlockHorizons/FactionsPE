<?php

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Text;
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
