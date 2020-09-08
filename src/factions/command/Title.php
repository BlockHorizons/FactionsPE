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
use factions\entity\IMember;
use factions\FactionsPE;
use factions\manager\Permissions;
use factions\permission\Permission;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use localizer\Localizer;
use dominate\parameter\Parmater;
use factions\command\requirement\FactionRequirement;
use factions\manager\Members;
use factions\command\parameter\MemberParameter;

class Title extends Command {

	public function setup() {
		$this->addParameter((new MemberParameter("player"))->setOptional(false));
		$this->addParameter((new Parameter("title", Parameter::TYPE_STRING))->setOptional(false));
	}

	public function perform(CommandSender $sender, $label, array $args) {
        /** @var IMember $you */
        $you = $this->getArgument(0);
        /** @var string $title */
		$title = $this->getArgument(1);

		$newTitle = Text::parse($title);
		if (!$you->getPlayer()->hasPermission(Permissions::TITLE_COLOR))
        {
            $newTitle = TextFormat::clean($newTitle);
        }

		if ( ! Permissions::getById(Permission::TITLE)->has(($msender = Members::get($sender)), $you->getFaction(), true)) return true;

		// Rank Check
		if (!$msender->isOverriding() && \factions\relation\Relation::isHigherThan($you->getRole(), $msender->getRole()))
        {
            return Text::parse("<b>You can not edit titles for higher ranks.");
        }

		// Event
		$event = new EventFactionsTitleChange($sender, $you, $newTitle);
		FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) return true;
		$newTitle = $event->getNewTitle();

		// Apply
		$you->setTitle($newTitle);
//
//		// Inform
//		$msender->getFaction()->sendMessage("%s<i> changed a title: %s", msender.describeTo(msenderFaction, true), you.describeTo(msenderFaction, true));

		return true;
	}

}
