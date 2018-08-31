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
use factions\command\Player as PlayerCommand;
use factions\FactionsPE;
use factions\manager\Permissions;
use factions\relation\Relation as Rel;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class FactionCommand extends Command {

	const CREATE_BUTTON      = 0;
	const DESCRIPTION_BUTTON = 1;
	const MAP_BUTTON         = 2;
	const COMMANDS_BUTTON    = 3;
	const INVITE_BUTTON      = 4;
	const LEAVE_BUTTON       = 5;
	const CLAIM_BUTTON       = 6;
	const UNCLAIM_BUTTON     = 7;
	const SEECHUNK_BUTTON    = 8;
	const ADMIN_BUTTON       = 9;

	private $buttons = ["create", "description", "map", "commands", "invite", "leave", "claim", "unclaim", "seechunk", "seechunk"];

	public function __construct(FactionsPE $plugin) {
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
			new Override($plugin, "admin", "Turn on overriding mode", Permissions::OVERRIDE, ["override"]),
			new Map($plugin, "map", "Show Factions map", Permissions::MAP),
			new Claim($plugin, "claim", "Claim this plot", Permissions::CLAIM),
			new Unclaim($plugin, "unclaim", "Unclaim this plot", Permissions::UNCLAIM),
			new Home($plugin, "home", "Teleport to faction home", Permissions::HOME),
			new SetHome($plugin, "sethome", "Set Faction home to your location", Permissions::SETHOME),
			new Chat($plugin, "chat", "Toggle faction chat mode", Permissions::CHAT),
			new Top($plugin, "top", "See top of most powerful factions", Permissions::TOP),
			new PlayerCommand($plugin, "player", "See more detailed info about someone", Permissions::PLAYER),
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
			new SeeChunk($plugin, "seechunk", "See chunk borders", Permissions::SEECHUNK, ["sc"]),
		];

		if ($plugin->economyEnabled()) {
			$childs[] = new Money($plugin, "money", "Manage faction bank account", Permissions::MONEY, ["bank", "cash"]);
		}

		foreach ($childs as $child) {
			$this->addChild($child);
		}
		foreach (Rel::getAll() as $rel) {
			if (Rel::isRankValid($rel)) {
				continue;
			}

			$this->addChild(new RelationSetQuick($this, $rel, "Set relation wish to " . $rel, Permissions::RELATION_SET));
		}

		$this->addParameter(new Parameter("command"));
	}

	/**
	 * @param CommandSender $sender
	 * @param string $label
	 * @param string[] $args
	 * @return bool
	 */
	public function prepare(CommandSender $sender, $label, array $args): bool {
		if (!empty($args)) {
			if (!$this->getChild($args[0])) {
				$this->sendUsage($sender);
				return false;
			}
		} elseif ($sender instanceof Player) {
			if ($this->getPlugin()->isFormsEnabled()) {
				$this->factionForm($sender);
				return false;
			}
		}
		return true;
	}

	public function factionForm(Player $player) {
		$fapi = $this->getPlugin()->getFormAPI();
		// Behaviour
		$form = $fapi->createSimpleForm(function (Player $player, int $result = null) {
			if ($result !== null) {
				switch ($result) {
				case self::CREATE_BUTTON:
					$this->getChild("create")->createForm($player);
					break;
				case self::DESCRIPTION_BUTTON:
					$this->getChild("description")->descriptionForm($player);
					break;
				case self::MAP_BUTTON:
					$this->getChild("map")->execute($player, "", []);
					break;
				case self::COMMANDS_BUTTON:
					$this->commandsForm($player);
					break;
				case self::INVITE_BUTTON:
					$this->getChild("invite")->inviteForm($player);
					break;
				case self::LEAVE_BUTTON:
					$this->getChild("leave")->leaveForm($player);
					break;
				case self::CLAIM_BUTTON:
					$this->getChild("claim")->claimForm($player);
					break;
				case self::UNCLAIM_BUTTON:
					$this->getChild("claim")->unclaimForm($player);
					break;
				case self::SEECHUNK_BUTTON:
					$this->getChild("seechunk")->execute($player, "", []);
					break;
				case self::ADMIN_BUTTON:
					$this->getChild("override")->execute($player, "", []);
					break;
				}
			}
		});
		// Title
		$form->setTitle(Localizer::trans("menu-title"));
		// Buttons
		foreach ($this->buttons as $name) {
			$color = $name !== "commands" && $player->hasPermission($this->getChild($name)->getPermission()) && $this->getChild($name)->testRequirements($player, true) ? "" : "<gray>";
			$form->addButton($color . Localizer::trans("button-" . $name));
		}
		// Show
		$form->sendToPlayer($player);
	}

	public function commandsForm(Player $player) {
		// TODO
	}

}
