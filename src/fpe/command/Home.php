<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\requirement\SimpleRequirement;
use fpe\command\requirement\FactionPermission;
use fpe\command\requirement\FactionRequirement;
use fpe\event\member\MemberHomeTeleportEvent;
use fpe\FactionsPE;
use fpe\flag\Flag;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\manager\Plots;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\relation\RelationParticipator;
use fpe\utils\Gameplay;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Home extends Command
{

    public function setup()
    {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::HOME)));
    }

    public function perform(CommandSender $sender, $label, array $args): bool
    {
        if (!$sender instanceof Player) return false;
        $member = Members::get($sender);
        if (!Gameplay::get("home.teleport-command-enabled", true)) {
            $sender->sendMessage(Localizer::translatable("home-teleport-command-disabled"));
            return true;
        }

        if (!$member->getFaction()->hasHome()) {
            $sender->sendMessage(Localizer::translatable('no-valid-home', ["faction" => $member->getFaction()->getName()]));
            if ($member->isPermitted(Permissions::getById(Permission::SETHOME)) and $sender->hasPermission(Permissions::SETHOME)) {
                $sender->sendMessage(Localizer::translatable('set-home-advice', [$this->getParent()->getChild("sethome")->getUsage()]));
            }
            return true;
        }

        if (!Gameplay::get("teleport-allowed-from-enemy-territory", false) && $member->isInEnemyTerritory()) {
            $sender->sendMessage(Localizer::translatable("cannot-tp-to-home-in-enemy-territory"));
            return true;
        }

        if (!Gameplay::get("home.teleport-allowed-from-different-world", true) && $member->getFaction()->getHome()->getLevel() !== $sender->getLevel()) {
            $sender->sendMessage(Localizer::translatable("cannot-tp-to-home-from-other-level"));
            return true;
        }

        $factionHere = Plots::getFactionAt($sender);
        // if player is not in a safe zone or their own faction territory, only allow teleport if no enemies are nearby
        if
        (
            ($max = Gameplay::get("homes.teleport-allowed-enemy-distance", 10)) > 0
            &&
            $factionHere->getFlag(Flag::PVP)
            &&
            (
                !$member->isInOwnTerritory()
                ||
                (
                    $member->isInOwnTerritory()
                    &&
                    !Gameplay::get("home.teleport-ignore-enemies-if-in-own-territory", false))
            )
        ) {
            foreach (Members::getAllOnline() as $otherPlayer) {
                if (!$otherPlayer instanceof RelationParticipator) continue;
                if ($member->getRelationTo($otherPlayer) !== Relation::ENEMY) continue;

                if ($sender->distance($otherPlayer->getPlayer()) < $max) {
                    $sender->sendMessage(Localizer::translatable("home-enemies-nearby", compact("max")));
                    return true;
                }
            }
        }
        $member->getFaction()->verifyHome();
        if (!$member->getFaction()->hasHome()) return false;

        $event = new MemberHomeTeleportEvent($member, $member->getFaction()->getHome());
        FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) return true;

        $home = $event->getDestination();
        $sender->teleport($home->getLevel()->getSafeSpawn());
        $sender->teleport($home);
        $sender->sendMessage(Localizer::translatable("home-success"));
        return true;
    }

}