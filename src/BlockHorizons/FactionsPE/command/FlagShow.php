<?php

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\parameter\FactionParameter;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\flag\Flag;
use BlockHorizons\FactionsPE\manager\Flags;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Pager;
use pocketmine\command\CommandSender;

class FlagShow extends Command
{

    public function setup()
    {
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
    }

    // -------------------------------------------- //
    // OVERRIDE
    // -------------------------------------------- //

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Parameters
        $faction = $this->getArgument(0, Members::get($sender)->getFaction());
        $page = $this->getArgument(1);

        // Pager create
        $pager = new Pager("flag-show-title", $page, 5, Flags::getAll(), $sender, function (Flag $flag, int $index, CommandSender $sender) use ($faction) {
            return $flag->getStateDesc($faction->getFlag($flag->getId()), true, true, true, true, true);
        });
        $pager->stringify();
        $pager->sendTitle($sender, [
            "faction" => $faction->getName(),
        ]);
        foreach ($pager->getOutput() as $line) {
            $sender->sendMessage($line);
        }
        return true;
    }

}
