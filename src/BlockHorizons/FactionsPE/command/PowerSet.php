<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.27.3
 * Time: 21:54
 */

namespace BlockHorizons\FactionsPE\command;


use BlockHorizons\FactionsPE\command\parameter\FactionParameter;
use BlockHorizons\FactionsPE\command\parameter\MemberParameter;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\localizer\Localizer;
use pocketmine\command\CommandSender;

class PowerSet extends Command
{

    public function setup()
    {
        $fp = new FactionParameter("target");
        $mp = new MemberParameter("member|faction");
        $this->addParameter($mp->setNext($fp));
        $this->addParameter(new Parameter("amount", Parameter::TYPE_INTEGER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $target = $this->getArgument(0);
        $amount = $this->getArgument(1);

        $target->setPowerBoost($amount);
        $sender->sendMessage(Localizer::trans("power-set", [
            "power-boost" => $target->getPowerBoost(),
            "target" => $target->getDisplayName()
        ]));
    }

}