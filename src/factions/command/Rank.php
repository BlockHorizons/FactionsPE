<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\command;

use dominate\Command;
use factions\command\parameter\FactionParameter;
use factions\command\parameter\MemberParameter;
use factions\command\parameter\RankParameter;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\relation\Relation;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Rank extends Command
{

    const RANK_REQUIRED = Relation::OFFICER;

    public function setup()
    {
        $this->addParameter(new MemberParameter("player", MemberParameter::ANY_MEMBER));
        $this->addParameter((new RankParameter("action|rank"))->setDefaultValue(null)->setPermission(Permissions::RANK_ACTION));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $this->sender = $sender;

        $member = $this->getArgument(0);
        $msender = Members::get($sender);
        $faction = $this->getArgument(2);

        // Show him the rank if no action is required
        if (!isset($args[1])) {
            if (!$sender->hasPermission(Permissions::RANK_SHOW)) {
                return "no-perm-to-see-member-rank";
            }
            # Show rank
            return ["member-rank", ["rank" => $member->getRole(), "member" => $member->getDisplayName()]];
        }
        $action = strtolower($this->getArgument(1));
        $targetRank = $action;
        if (!Relation::isRankValid($targetRank)) {
            # Check if this is promotion
            if (RankParameter::isPromotion($action)) {
                $targetRank = Relation::getNext($member->getRole());
            } elseif (RankParameter::isDemotion($action)) {
                $targetRank = Relation::getPrevious($member->getRole());
            }
            if (!Relation::isRankValid($targetRank)) {
                return "cant-demote-promote-border-rank";
            }
        }
        // Users may enter 'mem' as in short for 'member'
        $targetRank = Relation::fromString($targetRank);

        // Ensure allowed
        $factionChange = $faction !== $member->getFaction();
        if (!$msender->isOverriding()) {
            if ($faction->isNone()) {
                return "wilderness-doesnt-use-ranks";
            }
            if ($faction !== $member->getFaction()) {
                return ["target-must-be-in-same-faction", ["member" => $member->getDisplayName()]];
            }
            if ($msender === $member) {
                return "cant-change-self-rank";
            }
            if ($factionChange) {
                return ["cant-change-faction", ["member" => $member->getDisplayName()]];
            }
            if (Relation::isLowerThan($msender->getRole(), RankParameter::REQUIRED_RANK)) {
                return ["not-enough-rank-power", [RankParameter::REQUIRED_RANK]];
            }
            if ($member->getRole() === $msender->getRole()) {
                return ["cant-manage-each-other-ranks", ["rank" => $member->getRole()]];
            }
            if (Relation::isLowerThan($msender->getRole(), $targetRank)) {
                return "cant-set-rank-higher-than-own";
            }
            if ($msender->getRole() === $targetRank && $msender->getRole() !== Relation::LEADER) {
                return "cant-set-rank-equal-to-own";
            }
            if (Relation::isLowerThan($msender->getRole(), $member->getRole())) {
                return ["cant-manage-higher-ranks", ["rank" => $member->getRole(), "member" => $member->getDisplayName()]];
            }
            if ($member->getRole() === $targetRank) {
                return ["cant-change-no-sense", ["rank" => $member->getRole(), "member" => $member->getDisplayName()]];
            }
        }

        $faction->setRole($member, $targetRank);
        $member->updateFaction();
        $faction->sendMessage(Localizer::trans("rank-changed-inform-faction", [
            "new-rank" => $targetRank,
            "member" => $member->getDisplayName(),
            "sender" => $msender->getDisplayName()
        ]));
        return ["rank-changed-inform-sender", ["new-rank" => $targetRank, "member" => $member->getDisplayName()]];
    }

}
