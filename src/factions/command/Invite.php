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
use factions\command\requirement\FactionPermission;
use factions\FactionsPE;
use factions\manager\Permissions;
use factions\permission\Permission;
use localizer\Localizer;
use pocketmine\Player;

class Invite extends Command {

	public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases) {
		parent::__construct($plugin, $name, $description, $permission, $aliases);

		$this->addChild(new InviteAdd($plugin, "add", "Invite new member to your faction", Permissions::INVITE_ADD, ["invite"]));
		$this->addChild(new InviteList($plugin, "list", "List all active invitations for faction", Permissions::INVITE_LIST, ["ls"]));
		$this->addChild(new InviteRemove($plugin, "remove", "Delete an invitation", Permissions::INVITE_REMOVE, ["delete"]));

		$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
		$this->addRequirement(new FactionPermission(Permissions::getById(Permission::INVITE)));

		$this->addParameter(new Parameter("add|remove|list"));
	}

	/**
	 * TODO: Only invite?
	 */
	public function inviteForm(Player $player) {
		$fapi = $this->getPlugin()->getFormAPI();
		$form = $fapi->createCustomForm(function (Player $player, array $data) {
			$result = $data[0] ?? $data[1];

			if ($result !== null) {
				$this->execute($player, "", ["add", $result]);
			}

		});
		$options = array_values(array_map(function ($player) {
			return $player->getName();
		}, $this->getPlugin()->getServer()->getOnlinePlayers()));
		$form->setTitle(Localizer::trans("invite-form-title"));
		$form->addLabel(Localizer::trans("invite-form-content"));
		$form->addInput(Localizer::trans("invite-form-input"));
		$form->addLabel(Localizer::trans("invite-input-label"));
		$form->addDropdown(Localizer::trans("select-player"), $options);
		$form->sendToPlayer($player);
	}

}