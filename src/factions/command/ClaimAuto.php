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

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use factions\entity\Perm;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\objs\Plots;
use factions\permission\Permission;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class ClaimAuto extends ClaimOne
{

    public function perform(CommandSender $sender, $label, array $args): bool
    {
        $member = Members::get($sender);
        $faction = $this->getArgument($this->getFactionArgIndex());

        // Disable?
        if ($faction === null || $faction === $member->getAutoClaimFaction()) {
            $member->setAutoClaimFaction(null);
            $member->sendMessage(Text::parse("<i>Disabled auto-setting as you walk around."));
            return true;
        }

        // MPerm Preemptive Check
        if ($faction->isNormal() && !Permissions::getById(Permission::TERRITORY)->has($member, $faction)) return true;

        // Apply / Inform
        $member->setAutoClaimFaction($faction);
        $sender->sendMessage(Localizer::trans("<i>Now auto-setting <h>:faction<i> land.", ["faction" => $faction->getName()]));
        return true;
    }
}