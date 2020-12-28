<?php

/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\Player as PlayerCommand;
use fpe\FactionsPE;
use fpe\form\FactionForm;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\relation\Relation as Rel;
use fpe\utils\Gameplay;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class FactionCommand extends Command {

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
			new SeeChunk($plugin, "seechunk", "See chunk borders", Permissions::SEECHUNK, ["sc", "border"]),
			new FlagCommand($plugin, "flag", "Manage faction flags", Permissions::FLAG),
			new RankQuickset($plugin, "promote", "Promote member to higher rank", Permissions::PROMOTE, ["+"]),
			new RankQuickset($plugin, "demote", "Demote member to lower rank", Permissions::DEMOTE, ["-"])
		];

		if ($plugin->economyEnabled()) {
			$childs[] = new Money($plugin, "money", "Manage faction bank account", Permissions::MONEY, ["bank", "cash"]);
		}
		if(Gameplay::get("fly", false)) {
		    $childs[] = new Fly($plugin, "fly", "Be able to fly inside claimed territory", Permissions::FLY);
        }

		foreach ($childs as $child) {
			$this->addChild($child);
		}
		foreach (Rel::getAll() as $rel) {
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
	public function prepare(CommandSender $sender, string $label, array $args): bool {
		if (!empty($args)) {
			if (!$this->getChild($args[0])) {
				$this->sendUsage($sender);
				return false;
			}
		} elseif ($sender instanceof Player) {
			if ($this->getPlugin()->isFormsEnabled()) {
				if (Members::get($sender)->hasFaction()) {
					$this->getFormHandler()->factionForm($sender);
				} else {
					$this->getFormHandler()->factionlessForm($sender);
				}
				return false;
			}
		}
		return true;
	}

	public function sendUsage( ? CommandSender $sender = null) {
		$sender = $sender ?? $this->sender;
		$sender->sendMessage(Localizer::trans("command-usage", [
			"usage" => "/faction <sub-command>",
		]));
		$sender->sendMessage(Localizer::trans("faction-command-tip", ["help" => $this->getChild("help")->getUsage()]));
	}

	public function getFormHandler() : FactionForm {
		return $this->getPlugin()->getFormHandler();
	}

}
