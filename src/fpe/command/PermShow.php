<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\command\parameter\PermissionParameter;
use fpe\manager\Pemrissions;
use fpe\permission\Permission;
use fpe\utils\Pager;
use fpe\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class PermShow extends Command
{

    public function setup()
    {
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new PermissionParameter("perm", PermissionParameter::ANY))->setDefaultValue("all"));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $perms = $this->getArgument(1);
        if (!is_array($perms)) $perms = [$perms];

        $faction = $this->getArgument(2);
        $page = $this->getArgument(0);

        $pager = new Pager("perm-show-header", $page, 5, $perms, $sender, function (Permission $perm, int $i, CommandSender $sender) use ($faction) {
            return Text::parse($perm->getStateInfo($faction->getPermitted($perm), true));
        });
        $pager->stringify();

        $pager->sendTitle($sender, ["faction" => $faction->getName()]);

        $sender->sendMessage(Permission::getStateHeaders());
        foreach ($pager->getOutput() as $line) $sender->sendMessage($line);

        return true;
    }
}