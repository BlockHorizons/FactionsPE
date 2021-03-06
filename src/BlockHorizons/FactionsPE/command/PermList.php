<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use BlockHorizons\FactionsPE\utils\Pager;
use pocketmine\command\CommandSender;

class PermList extends Command
{

    public function setup()
    {
        // Parameters
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER, 1))->setDefaultValue(1));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Args
        $page = $this->getArgument(0);

        // Create messages
        $perms = [];
        $member = Members::get($sender);
        foreach (Permissions::getAll() as $perm) {
            if (!$perm->isVisible() && !$member->isOverriding()) {
                continue;
            }

            $perms[] = $perm;
        }

        $pager = new Pager("perm-list-header", $page, 5, $perms, $sender, function (Permission $perm, int $i, CommandSender $sender) {
            return Localizer::translatable("perm-list-line", [$i, $perm->getDescription(), $perm->getName()]);
        });

        // Send messages
        $pager->stringify();
        $pager->sendTitle($sender);

        foreach ($pager->getOutput() as $line) {
            $sender->sendMessage($line);
        }

        return true;
    }

}