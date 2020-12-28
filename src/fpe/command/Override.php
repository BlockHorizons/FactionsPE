<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\utils\Gameplay;
use fpe\utils\Text;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;

class Override extends Command
{

    public function setup()
    {
        $this->addParameter((new Parameter("on|off", Parameter::TYPE_BOOLEAN))->setDefaultValue(null));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $msender = Members::get($sender);

        $msender->setOverriding((bool)$value = $this->getArgument(0) ?? !$msender->isOverriding());
        $sender->sendMessage(Localizer::translatable("overriding-" . ($value ? "enabled" : "disabled")));

        if (Gameplay::get("log.override", true)) {
            FactionsPE::get()->getLogger()->notice(Localizer::trans("log.override", [
                "player" => $msender->getName(),
                "overriding" => Text::toString($value, true)
            ]));
        }

        return true;
    }

}