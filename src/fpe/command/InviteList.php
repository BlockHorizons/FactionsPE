<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\utils\Pager;
use fpe\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class InviteList extends Command
{

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Args
        $msender = Members::get($sender);
        $page = $this->getArgument(0);
        $faction = $this->getArgument(1);

        // If sender wants to view other faction invites but lacks permission, stop here
        if ($faction !== $msender->getFaction() && !$sender->hasPermission(Permissions::INVITE_LIST_OTHER)) return false;

        // Check permission
        if (($perm = Permissions::getById(Permission::INVITE)) && !$perm->has($msender, $faction)) {
            $sender->sendMessage(Localizer::translatable("no-permission-to-view-invite-list", [$faction->getName()]));
            return false;
        }

        // Pager Create
        $players = $faction->getInvitedPlayers();
        $pager = new Pager(Text::titleize("Invited Players List"), $page, 5, $players, $sender, $stringifier = function ($player, int $index, CommandSender $sender) {
            if (($target = Members::get($player, false))) {
                $targetName = $target->getDisplayName();
                $isAre = "is";
                $targetRank = $target->getRole();
                $targetFaction = $target->getFaction();
                $theAan = $targetRank === Relation::LEADER ? "the" : Text::aan($targetRank);
                $rankName = strtolower(Text::getNicedEnum($targetRank));
                $ofIn = $targetRank === Relation::LEADER ? "of" : "in";
                $factionName = $targetFaction->getName();
                return Text::parse(sprintf("%s <i>%s %s <h>%s <i>%s %s<i>.", $targetName, $isAre, $theAan, $rankName, $ofIn, $factionName));
            } else {
                return Text::parse($player);
            }
        });
        $pager->stringify();

        // Pager Message
        $pager->sendTitle($sender);

        foreach ($pager->getOutput() as $l) $sender->sendMessage($l);

        return true;
    }
}