<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\manager\Members;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Description extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("...description", Parameter::TYPE_STRING));
        //$this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (!($m = Members::get($sender))->isLeader() && !$m->isOverriding()) {
            return ["requirement.faction-permission-error", ["perm_desc" => "set description"]]; # Not fully translatable TODO
        }

        $description = implode(" ", $args);

        if (strlen($description) > 62) {
            return "description-too-long";
        }

        $faction = $m->getFaction();

        $faction->setDescription($description);

        $faction->sendMessage(Localizer::translatable("description-updated", [
            "player" => $m->getDisplayName(),
        ]));
        $faction->sendMessage(Localizer::translatable("new-description", [
            "description" => $description,
        ]));
        return true;
    }

    public function descriptionForm(Player $player)
    {
        $fapi = $this->getPlugin()->getFormAPI();
        $form = $fapi->createCustomForm(function (Player $player, array $data) {
            $result = $data[0];
            if ($result) {
                $this->execute($player, "", [$result]);
            }
        });

        $form->setTitle(Localizer::trans("description-form-title"));
        $form->addInput(Localizer::trans("description-form-input"));
        $form->sendToPlayer($player);
    }

}