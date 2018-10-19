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
use factions\command\parameter\FactionParameter;
use factions\entity\IMember;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\permission\Permission;
use factions\utils\Pager;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Status extends Command
{

    public function setup()
    {
        $this->addParameter((new FactionParameter("faction", true))->setDefaultValue("self"));
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
    }


    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);
        $faction = $this->getArgument(0);
        $page = $this->getArgument(1);

        if ($msender->isNone() && $faction->isNone() && !isset($args[0])) {
            return "enter-faction-for-status";
        }

        if (!($perm = Permissions::getById(Permission::STATUS))->has($msender, $faction)) {
            return ["faction-permission-error", ["perm_desc" => $perm->getDescription()]];
        }

        $members = $faction->getMembers();
        if (empty($members)) {
            return ["faction-empty-cant-status", ["faction" => $faction->getName()]];
        }

        $pager = new Pager("faction-status-header", $page, $sender instanceof Player ? 5 : PHP_INT_MAX, $members, $sender, function (IMember $player, int $i, CommandSender $sender) {

            // Name
            $displayName = $player->getDisplayName();
            $length = 15 - strlen($displayName);
            $length = $length < 1 ? $length = 1 : $length;
            $space = str_repeat(" ", $length);

            // Power
            $power = $player->getPower();
            $maxPower = $player->getPowerMax();
            $percent = $power / $maxPower;

            if ($percent > 0.75) $color = TextFormat::GREEN;
            elseif ($percent > 0.5) $color = TextFormat::YELLOW;
            elseif ($percent > 0.25) $color = TextFormat::RED;
            else $color = TextFormat::DARK_RED;

            $power = Text::parse("<art>Power: " . $color . $power . "<gray>/" . $maxPower . "<white>");
            $lastActive = ($player->isOnline() ? "<lime>Online right now." : "<i>Last played: " . Text::ago($player->getLastPlayed()));

            return Text::parse("$displayName{$space} $power $lastActive", $displayName, $space, $power, $lastActive);

        });
        $pager->stringify();


        $pager->sendTitle($sender, ["faction" => $faction->getName()]);
        foreach ($pager->getOutput() as $m) $sender->sendMessage($m);
        return true;
    }


}
