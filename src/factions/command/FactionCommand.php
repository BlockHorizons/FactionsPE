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
use factions\FactionsPE;
use factions\manager\Permissions;
use factions\relation\Relation as Rel;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class FactionCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, 'faction', 'Main Faction command', Permissions::MAIN, ['fac', 'f']);

        // Registering subcommands
        $childs = [
            new Help($plugin, "help", "Show FactionsPE command help page", Permissions::HELP, ["?"]),
            new CreateFaction($plugin, 'create', 'Create a new faction', Permissions::CREATE, ['make', 'new']),
            new LeaveFaction($plugin, 'leave', 'Leave your current faction', Permissions::LEAVE, ['quit']),
            new Invite($plugin, "invite", "Invite someone to your faction", Permissions::INVITE, ["inv"]),
            new Join($plugin, "join", "Join to a faction", Permissions::JOIN),
            new Close($plugin, "close", "Allow only invited players to join", Permissions::CLOSE),
            new Open($plugin, "open", "Let anyone join", Permissions::OPEN),
            new Kick($plugin, "kick", "Kick a member from a faction", Permissions::KICK),
            new Override($plugin, "override", "Turn on overriding mode", Permissions::OVERRIDE, ["admin"]),
            new Map($plugin, "map", "Show Factions map", Permissions::MAP),
            new Claim($plugin, "claim", "Claim this plot", Permissions::CLAIM),
            new Unclaim($plugin, "unclaim", "Unclaim this plot", Permissions::UNCLAIM),
            new Home($plugin, "home", "Teleport to faction home", Permissions::HOME),
            new SetHome($plugin, "sethome", "Set Faction home to your location", Permissions::SETHOME),
            new Chat($plugin, "chat", "Toggle faction chat mode", Permissions::CHAT),
            new Top($plugin, "top", "See top of most powerful factions", Permissions::TOP),
            new Player($plugin, "player", "See more detailed info about someone", Permissions::PLAYER),
            new Perm($plugin, "permission", "Manage faction permissions", Permissions::PERM),
            new Disband($plugin, "disband", "Disband a faction", Permissions::DISBAND, ["destroy"]),
            new Status($plugin, "status", "Check members in the faction", Permissions::STATUS),
            new ListCmd($plugin, "list", "See list of all created factions", Permissions::LIST),
            new Reload($plugin, "reload", "Reload config file", Permissions::RELOAD),
            new Rank($plugin, "rank", "Manage member ranks", Permissions::RANK),
            new Relation($plugin, "relation", "Manage relations", Permissions::RELATION),
            new HudSwitch($plugin, "hud", "Toggle HUD", Permissions::HUD),
            new Power($plugin, "power", "Manage power", Permissions::POWERBOOST),
            new Version($plugin, "version", "See current plugin version and information", Permissions::VERSION),
            new Name($plugin, "name", "Rename faction", Permissions::NAME),
            new Info($plugin, "info", "Get faction information", Permissions::INFO),
            new Description($plugin, "description", "Set faction's description", Permissions::DESCRIPTION),
            new SeeChunk($plugin, "seechunk", "See chunk borders", Permissions::SEECHUNK, ["sc"])
        ];

        if ($plugin->economyEnabled()) {
            $childs[] = new Money($plugin, "money", "Manage faction bank account", Permissions::MONEY, ["bank", "cash"]);
        }

        foreach ($childs as $child) {
            $this->addChild($child);
        }
        foreach (Rel::getAll() as $rel) {
            if(Rel::isRankValid($rel)) continue;
            $this->addChild(new RelationSetQuick($this, $rel, "Set relation wish to ".$rel, Permissions::RELATION_SET));
        }

        $this->addParameter(new Parameter("command"));
    }


    /**
     * @param CommandSender $sender
     * @param string $label
     * @param string[] $args
     * @return bool
     */

    public function perform(CommandSender $sender, $label, array $args)
    {
        if ($this->endPoint !== $this) return true;

        if (isset($args[0])) {
            if (!$this->getChild($args[0])) {
                $sender->sendMessage(Localizer::translatable("command.generic-usage", [$args[0]]));
            }
        }
        return true;
    }

}
