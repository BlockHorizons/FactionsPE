<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.5.4
 * Time: 22:58
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\command\parameter\FactionParameter;
use fpe\command\parameter\RelationParameter;
use fpe\entity\Faction;
use fpe\event\faction\FactionRelationChangeEvent;
use fpe\flag\Flag;
use fpe\manager\Members;
use fpe\relation\Relation as Rel;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;

class RelationSet extends Command {

	public function setup() {
		$this->addParameter(new FactionParameter("faction|member", true));
		$this->addParameter(new RelationParameter("relation", RelationParameter::RELATION));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		/** @var Faction $faction */
		$faction = $this->getArgument(0);
		/** @var string $relation */
		$relation         = $this->getArgument(1);
		$fsender          = Members::get($sender);
		$senderFaction    = $fsender->getFaction();
		$previousRelation = $senderFaction->getRelationTo($faction);

		// Do safety checks
		if ($senderFaction === $faction) {
			return "cant-relate-to-self";
		}
		if ($previousRelation === $relation) {
			return ["relation-set-duplicate", [
				"faction" => $faction->getName(),
			]];
		}

		// Call the event
		$event = new FactionRelationChangeEvent($sender, $senderFaction, $faction, $relation);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) {
			return false;
		}

		// Apply new relation wish
		$senderFaction->setRelationWish($faction, $event->getNewRelation());

		// Check if relation change were successful
		if ($senderFaction->getRelationTo($faction) === $relation) {
			$sender->sendMessage(Localizer::translatable("relation-set", [
				"faction"  => $faction->getName(),
				"relation" => $event->getNewRelation(),
			]));
			$faction->sendMessage(Localizer::translatable("relation-set", [
				"faction"  => $senderFaction->getName(),
				"relation" => $event->getNewRelation(),
			]));
		} else {
			// Send information message
			// enemy = 8000
			// ally = 5000
			if (Rel::isLowerThan($event->getNewRelation(), $previousRelation)) {
				$faction->sendMessage(Localizer::translatable("relation-wish-request", [
					"faction"  => $senderFaction->getName(),
					"relation" => $event->getNewRelation(),
					"command"  => "/faction " . $event->getNewRelation() . " " . $senderFaction->getName(),
				]));
				$sender->sendMessage(Localizer::trans("relation-wish-request-sent", [
					"faction"  => $faction->getName(),
					"relation" => $event->getNewRelation(),
				]));
			} else {
				if ($faction->getFlag(Flag::PEACEFUL)) {
					return ["cant-set-relation-cuz-faction-peaceful", ["faction" => $faction->getName()]];
				}
			}
		}

		return true;
	}

}