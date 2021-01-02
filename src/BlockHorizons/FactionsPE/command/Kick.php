<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\parameter\MemberParameter;
use BlockHorizons\FactionsPE\command\requirement\FactionPermission;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\event\member\MembershipChangeEvent;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use BlockHorizons\FactionsPE\relation\Relation;
use BlockHorizons\FactionsPE\utils\Gameplay;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Kick extends Command
{

    public function setup()
    {
        $this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::KICK)));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (!$sender instanceof Player) return false;
        $target = $this->getArgument(0);
        $msender = Members::get($sender);
        $overriding = $msender->isOverriding();

        // Validate
        if ($target === $msender) {
            $sender->sendMessage(Localizer::translatable("cant-kick-yourself", [
                "command" => $this->getParent()->getChild("leave")->getUsage()
            ]));
            return true;
        }
        if ($target->isLeader() && !$overriding) {
            $sender->sendMessage(Localizer::translatable("cant-kick-leader", [
                "leader" => $target->getDisplayName()
            ]));
            return true;
        }
        if (Relation::isHigherThan($target->getRole(), $msender->getRole()) && !$overriding) {
            $sender->sendMessage(Localizer::translatable("cant-kick-higher-rank", [
                "who" => $target->getDisplayName()
            ]));
            return true;
        }
        if ($target->getRole() === $msender->getRole() && !$overriding) {
            $sender->sendMessage(Localizer::translatable("cant-kick-same-rank", [
                "who" => $target->getDisplayName(),
            ]));
            return true;
        }
        if (Gameplay::get("can-leave-with-negative-power", false) && $target->getPower() < 0 && !$overriding) {
            $sender->sendMessage(Localizer::translatable("cant-kick-player-with-negative-power", [
                "who" => $target->getDisplayName()
            ]));
            return true;
        }

        $faction = $target->getFaction();
        if (!$faction->isPermitted($faction->getRelationTo($msender), Permissions::getById(Permission::KICK))) return false;

        $event = new MembershipChangeEvent($target, $faction, MembershipChangeEvent::REASON_KICK);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) return false;

        $faction->sendMessage(Localizer::translatable("member-kicked-inform-faction", [
            "by" => $sender->getDisplayName(),
            "who" => $target->getDisplayName(),
        ]));
        $target->sendMessage(Localizer::translatable("member-kicked-inform-target", [
            "by" => $sender->getDisplayName(),
            "faction" => $faction->getName()
        ]));
        if ($target->getFaction() !== $msender->getFaction()) {
            $target->sendMessage(Localizer::translatable("member-kicked-from-other-faction", [
                "faction" => $faction->getName(),
                "who" => $target->getDisplayName()
            ]));
        }

        if (Gameplay::get("log.member-kick", true)) {
            FactionsPE::get()->getLogger()->info(Localizer::trans("log.member-kick", [
                "by" => $sender->getDisplayName(),
                "who" => $target->getDisplayName(),
                "faction" => $faction->getName()
            ]));
        }

        // If sender managed to kick leader from his faction then lets promote new one
        if ($target->isLeader()) {
            $faction->promoteNewLeader();
        }
        $faction->setInvited($target, false);
        $faction->removeMember($target);
        $target->resetFactionData();
    }

}