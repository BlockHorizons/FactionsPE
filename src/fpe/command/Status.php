<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */
namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\entity\IMember;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\utils\Pager;
use fpe\utils\Text;
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
