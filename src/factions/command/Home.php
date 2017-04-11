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
use dominate\requirement\SimpleRequirement;
use factions\command\requirement\FactionPermission;
use factions\command\requirement\FactionRequirement;
use factions\event\member\MemberHomeTeleportEvent;
use factions\FactionsPE;
use factions\flag\Flag;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\manager\Plots;
use factions\permission\Permission;
use factions\relation\Relation;
use factions\utils\Gameplay;
use localizer\Localizer;
use pocketmine\command\CommandSender;

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