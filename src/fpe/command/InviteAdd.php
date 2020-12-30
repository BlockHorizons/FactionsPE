<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\command\parameter\MemberParameter;
use fpe\command\requirement\FactionRequirement;
use fpe\dominate\Command;
use fpe\dominate\requirement\SimpleRequirement;
use fpe\FactionsPE;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class InviteAdd extends Command
{

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = [])
    {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addParameter(new MemberParameter("member", MemberParameter::ANY_MEMBER));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
    }

    public function perform(CommandSender $sender, $label, array $args): bool
    {
        if (!$sender instanceof Player) return false;
        $member = $this->getArgument(0);
        $msender = Members::get($sender);
        $faction = $msender->getFaction();

        if ($faction->isInvited($member)) {
            $sender->sendMessage(Localizer::translatable('player-already-invited', [$member->getDisplayName()]));
            return true;
        }
        if ($member->hasFaction()) {
            $sender->sendMessage(Localizer::translatable('player-in-faction', [$member->getDisplayName()]));
            return true;
        }

        $faction->setInvited($member, true);
        $sender->sendMessage(Localizer::translatable('invite-add-success', [$member->getDisplayName()]));
        if ($member->isOnline()) {
            $member->sendMessage(Localizer::trans('invite-add-inform-target', [
                "player" => $sender->getDisplayName(),
                "faction" => $faction->getName()
            ]));
            $member->sendMessage(Localizer::trans('invite-add-inform-target-suggestion', [
                "command" => "/" . $this->getRoot()->getChild("join")->getName() . " " . $faction->getName()
            ]));
        }
        return true;
    }

}