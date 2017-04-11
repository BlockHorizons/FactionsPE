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

namespace factions\command\requirement;

use dominate\requirement\SimpleRequirement;
use factions\manager\Members;
use factions\permission\Permission;
use localizer\Translatable;
use pocketmine\command\CommandSender;

class FactionPermission extends SimpleRequirement
{

    /** @var Permission */
    public $permission;

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
        $r = $member->getFaction()->isPermitted($member->getRole(), $this->permission);
        if (!$r && !$silent) {
            $sender->sendMessage($this->createErrorMessage($sender));
        }
        return $r;
    }

    public function createErrorMessage(CommandSender $sender = null): Translatable
    {
        return new Translatable("requirement.faction-permission-error", [
            'perm_desc' => $this->permission->getDescription()]);
    }

}