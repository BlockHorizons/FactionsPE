<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command\requirement;

use fpe\dominate\requirement\SimpleRequirement;
use fpe\manager\Members;
use fpe\permission\Permission;
use fpe\localizer\Translatable;
use pocketmine\command\CommandSender;

class FactionPermission extends SimpleRequirement
{

    /** @var Permission */
    public $permission;

    public $faction;

    # TODO: Add faction

    /**
     * @param Permission $permission
     */
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function hasMet(CommandSender $sender, $silent = false): bool
    {
        $member = Members::get($sender);
        $faction = $faction ?? $member->getFaction();
        $this->faction = $faction;
        $r = $faction->isPermitted($member->getRole(), $this->permission);
        if (!$r && !$silent) {
            $sender->sendMessage($this->createErrorMessage($sender));
        }
        return $r;
    }

    public function createErrorMessage(CommandSender $sender = null): Translatable
    {
        return new Translatable("requirement.faction-permission-error", [
            'perm_desc' => $this->permission->getDescription(), 'faction' => $this->faction->getName()]);
    }

}