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
use dominate\requirement\SimpleRequirement;

use factions\command\requirement\FactionRequirement;
use factions\entity\Member;
use factions\manager\Members;
use factions\FactionsPE;
use factions\command\parameter\MemberParameter;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use localizer\Localizer;

class InviteAdd extends Command {

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = []) {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));
        
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
    }

    public function execute(CommandSender $sender, $label, array $args) {
        if(!parent::execute($sender, $label, $args)) return false;

        $member = $this->getArgument(0);
        $msender = Members::get($sender);
        $faction = $msender->getFaction();

        if($faction->isInvited($member)) {
            $sender->sendMessage(Localizer::translatable('player-already-invited', [$member->getDisplayName()]));
            return true;
        }
        if($member->hasFaction()) {
            $sender->sendMessage(Localizer::translatable('player-in-faction', [$member->getDisplayName()]));
            return true;
        }

        $faction->setInvited($member, true);
        $sender->sendMessage(Localizer::translatable('invite-add-success', [$member->getDisplayName()]));
        if($member->isOnline()) $p->sendMessage(Localizer::parse('invite-add-inform-target', [
            "player" => $sender->getDisplayName(), 
            "faction" => $faction->getName()
            ]));
        return true;
    }

}