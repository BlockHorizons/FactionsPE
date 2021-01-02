<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\requirement\FactionPermission;
use BlockHorizons\FactionsPE\command\requirement\FactionRequirement;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use BlockHorizons\FactionsPE\utils\Gameplay;
use pocketmine\command\CommandSender;

class Deposit extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("amount", Parameter::TYPE_INTEGER));
        //$this->addParameter((new FactionParameter("faction"))->setDefaultValue("self")->setPermission(Permissions::MONEY_BALANCE_ANY));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::DEPOSIT)));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $faction = Members::get($sender)->getFaction();
        $amount = $this->getArgument(0);

        // Validate amount
        if ($amount < 0) {
            return "deposit-negative";
        }
        if ($amount > FactionsPE::get()->getEconomy()->balance($sender->getName())) {
            return "member-not-enough-money";
        }

        $faction->addToBank($amount);
        FactionsPE::get()->getEconomy()->takeMoney($sender->getName(), $amount);

        if (Gameplay::get("log.money-transactions", true)) {
            FactionsPE::get()->getLogger()->notice(Localizer::trans("log.money-deposit", [
                "faction" => $faction->getName(),
                "amount" => $amount,
                "player" => $sender->getName()
            ]));
        }

        return ["faction-deposit", compact("amount")];
    }

}
