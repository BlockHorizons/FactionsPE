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
use dominate\parameter\Parameter;

use factions\permission\Permission;
use factions\manager\Permissions;
use factions\manager\Members;
use factions\utils\Text;
use factions\utils\Pager;

use pocketmine\command\CommandSender;

use localizer\Localizer;

class PermList extends Command {

    public function setup() {
        // Parameters
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER, 1))->setDefaultValue(1));
    }

	public function perform(CommandSender $sender, $label, array $args) {
        // Args
        $page = $this->getArgument(0);

		// Create messages
		$perms = [];
        $member = Members::get($sender);
		foreach (Permissions::getAll() as $perm) {
            if (!$perm->isVisible() && !$member->isOverriding()) continue;
            $perms[] = $perm;
        }

        $pager = new Pager("perm-list-header", $page, 5, $perms, $sender, function(Permission $perm, int $i, CommandSender $sender) {
            return Localizer::translatable("perm-list-line", [$i, $perm->getDescription(), $perm->getName()]);
        });

		// Send messages
        $pager->stringify();

        $sender->sendMessage(Text::titleize(Localizer::translatable($pager->getHeader(), [$pager->getPage(), $pager->getMax()])));
		foreach($pager->getOutput() as $line) $sender->sendMessage($line);
        
        return true;
	}

}