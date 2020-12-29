<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\command\parameter\FactionParameter;
use fpe\dominate\Command;
use fpe\entity\Faction;
use fpe\event\faction\FactionDisbandEvent;
use fpe\FactionsPE;
use fpe\flag\Flag;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\utils\Gameplay;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Disband extends Command
{

    const BUTTON_NO = 1;
    const BUTTON_YES = 0;

    public function setup()
    {
        // Parameters
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function disbandForm(Player $player)
    {
        if (!$this->testRequirements($player)) {
            return;
        }

        $fapi = $this->getPlugin()->getFormAPI();
        $form = $fapi->createSimpleForm(function (Player $player, int $result = 0) {
            if ($result !== null) {
                if ($result === self::BUTTON_YES) {
                    $this->perform($player, "", []);
                } elseif ($result === self::BUTTON_NO) {
                    return;
                }
            }
        });

        $form->addButton(Localizer::trans("button-yes"));
        $form->addButton(Localizer::trans("button-no"));
        $form->sendToPlayer($player);
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Args
        /** @var Faction $faction */
        $faction = $this->getArgument(0);
        $member = Members::get($sender);

        if (!$faction) {
            return 'in-faction-error';
        }

        // Perm
        if (!($perm = Permissions::getById(Permission::DISBAND))->has($member, $faction)) {
            return ["faction-permission-error", ["perm_desc" => $perm->getDescription()]];
        }

        // Verify
        if ($faction->getFlag(Flag::PERMANENT)) {
            return "cant-disband-permanent";
        }

        // Event
        $event = new FactionDisbandEvent($member, $faction);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) {
            return false;
        }

        // Merged Apply and Inform
        $faction->disband(Faction::DISBAND_REASON_COMMAND);

        // Inform
        foreach ($faction->getOnlineMembers() as $member) {
            $member->sendMessage(Localizer::translatable("faction-disbanded-inform-member", [$member->getName()]));
        }

        if ($member->getFaction() != $faction) {
            return ["you-disbanded", [$faction->getName()]];
        }

        // Log
        if (Gameplay::get("log.faction-disband", true)) {
            FactionsPE::get()->getLogger()->notice(Localizer::translatable("log.faction-disband-by-command", $faction->getName(), $faction->getId(), $sender->getDisplayName()));
        }

        // Apply
        $faction->detach();
        return true;
    }

}
