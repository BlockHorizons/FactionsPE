<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\requirement\FactionPermission;
use BlockHorizons\FactionsPE\command\requirement\FactionRequirement;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use BlockHorizons\FactionsPE\utils\Text;
use pocketmine\command\CommandSender;

class ClaimAuto extends ClaimOne
{

    /** @var FactionRequirement */
    protected $requirement;

    public function setup()
    {
        $this->requirement = new FactionPermission(Permissions::getById(Permission::TERRITORY));
    }

    public function perform(CommandSender $sender, $label, array $args): bool
    {
        $member = Members::get($sender);
        $faction = $this->getArgument($this->getFactionArgIndex());

        // Disable?
        if (!isset($args[0]) && $member->getAutoClaimFaction() !== null) {
            $member->setAutoClaimFaction(null);
            $member->sendMessage(Text::parse("<i>Disabled auto-setting as you walk around."));
            return true;
        }

        // Permission Preemptive Check
        if (!$this->requirement->hasMet($sender, false)) {
            return true;
        }

        if ($faction === null) {
            $member->sendMessage(Text::parse("<red>Invalid faction!"));
            return true;
        }

        // Apply / Inform
        $member->setAutoClaimFaction($faction);
        $sender->sendMessage(Localizer::trans("<i>Now auto-setting <h>:faction<i> land.", ["faction" => $faction->getName()]));
        return true;
    }
}
