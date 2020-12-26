<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\entity\IMember;
use fpe\FactionsPE;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\utils\Text;
use pocketmine\command\CommandSender;
use localizer\Localizer;
use dominate\parameter\Parmater;
use fpe\command\requirement\FactionRequirement;
use fpe\manager\Members;
use fpe\command\parameter\MemberParameter;

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
		if (!$msender->isOverriding() && \fpe\relation\Relation::isHigherThan($you->getRole(), $msender->getRole()))
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
