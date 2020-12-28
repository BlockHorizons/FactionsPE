<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\command\parameter\MemberParameter;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;

class InviteRemove extends Command
{

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);
        $player = $this->getArgument(0);

        $faction = $msender->getFaction();
        if ($faction->isInvited($player)) {
            $faction->setInvited($player, false);
            $sender->sendMessage(Localizer::translatable("invitation-deleted", [$player->getDisplayName(), $faction->getName()]));
        } else {
            $sender->sendMessage(Localizer::translatable("not-invited", [$player->getDisplayName()]));
        }

        return true;
    }

}