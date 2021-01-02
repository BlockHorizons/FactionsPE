<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Permissions;

class Money extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("balance|deposit|withdraw"));

        $plugin = $this->getPlugin();
        $this->addChild(new Balance($plugin, "balance", "See faction money", Permissions::MONEY_BALANCE));
        $this->addChild(new Deposit($plugin, "deposit", "Deposit your money into faction bank", Permissions::MONEY_DEPOSIT));
        $this->addChild(new Withdraw($plugin, "withdraw", "Withdraw from faction bank account", Permissions::MONEY_WITHDRAW));
    }

}
