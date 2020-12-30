<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\command\requirement\FactionPermission;
use fpe\command\requirement\FactionRequirement;
use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\dominate\requirement\SimpleRequirement;
use fpe\FactionsPE;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\utils\Gameplay;
use pocketmine\command\CommandSender;

class Withdraw extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("amount", Parameter::TYPE_INTEGER));
        //$this->addParameter((new FactionParameter("faction"))->setDefaultValue("self")->setPermission(Permissions::MONEY_BALANCE_ANY));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::WITHDRAW)));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $faction = Members::get($sender)->getFaction();
        $amount = $this->getArgument(0);

        // Validate amount
        if ($amount < 0) {
            return "withdraw-negative";
        }
        if ($amount > $faction->getBank()) {
            return "faction-not-enough-money";
        }

        if (Gameplay::get("log.money-transactions", true)) {
            FactionsPE::get()->getLogger()->notice(Localizer::trans("log.money-withdraw", [
                "faction" => $faction->getName(),
                "amount" => $amount,
                "player" => $sender->getName()
            ]));
        }

        $faction->addToBank(-$amount);
        FactionsPE::get()->getEconomy()->addMoney($sender->getName(), $amount);
        return ["faction-withdraw", compact("amount")];
    }

}
