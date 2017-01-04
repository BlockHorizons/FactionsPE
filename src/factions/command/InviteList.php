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

use factions\entity\Faction;
use factions\entity\Member;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\permission\Permission;
use factions\relation\Relation;
use factions\utils\Pager;
use factions\utils\Text;
use factions\FactionsPE;
use factions\command\parameter\FactionParameter;

use pocketmine\command\CommandSender;

class InviteList extends Command {

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = []) {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
    }

    public function perform(CommandSender $sender, $label, array $args) {
        // Args
        $msender = Members::get($sender);
        $page = $this->getArgument(0);
		$faction = $this->getArgument(1);
		
        // If sender wants to view other faction invites but lacks permission, stop here
		if ( $faction !== $fsender->getFaction() && ! $sender->hasPermission(Permissions::INVITE_LIST_OTHER)) return false;
		
        // Check permission
		if ( ($perm = Permissions::getById(Permission::INVITE)) && !$perm->has($msender, $faction)) {
            $sender->sendMessage(Localizer::translatable("no-permission-to-view-invite-list", [$faction->getName()]));
            return false;
        }
		
		// Pager Create
		$players = $faction->getInvitedPlayers();
		$pager = new Pager(Text::titleize("Invited Players List"), $page, 5, $players, $stringifier = function($player, int $index, CommandSender $sender){
                if(($target = Members::get($player, false))) {
                    $targetName = $target->getDisplayName();
                    $isAre = "is";
                    $targetRank = $target->getRole();
                    $targetFaction = $target->getFaction();
                    $theAan = $targetRank === Rel::LEADER ? "the" : Text::aan($targetRank);
                    $rankName = strtolower(Text::getNicedEnum($targetRank));
                    $ofIn = $targetRank === Rel::LEADER ? "of" : "in";
                    $factionName = $targetFaction->getName();
                    return Text::parse(sprintf("%s <i>%s %s <h>%s <i>%s %s<i>.", $targetName, $isAre, $theAan, $rankName, $ofIn, $factionName));
                } else {
                    return Text::parse($player);
                }
        }, $sender);
        $pager->stringify();
		
		// Pager Message
        $sender->sendMessage($pager->getHeader());
        foreach($pager->getOutput() as $l) $sender->sendMessage($l);
        
        return true;
    }
}