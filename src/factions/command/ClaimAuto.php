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

use factions\entity\Perm;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\objs\Plots;
use factions\permission\Permission;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use factions\command\requirement\FactionPermission;

class ClaimAuto extends ClaimOne
{

    /** @var FactionRequirement */
    protected $requirement;

    public function setup() {
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
        if (!$this->requirement->hasMet($member, false)) {
            return true;
        }

        // Apply / Inform
        $member->setAutoClaimFaction($faction);
        $sender->sendMessage(Localizer::trans("<i>Now auto-setting <h>:faction<i> land.", ["faction" => $faction->getName()]));
        return true;
    }
}
