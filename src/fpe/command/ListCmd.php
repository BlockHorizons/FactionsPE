<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\manager\Factions;
use fpe\manager\Members;
use fpe\utils\Pager;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class ListCmd extends Command
{

    public function setup()
    {
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1)); // /f list [page=1]
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $page = $this->getArgument(0);
        $factions = Factions::getAll();

        $pager = new Pager("list-header", $page, $sender instanceof ConsoleCommandSender ? 15 : 5, $factions, $sender, function ($faction, $i, $sender) {
            if ($faction->isNone()) {
                return Localizer::trans("list-wilderness", [count(Members::getFactionless()) - 1]); // minus one, because of CONSOLE object
                # <i>Factionless<i> %d online
            } else {
                if ($faction->isSpecial() && !Members::get($sender)->isOverriding()) {
                    return null;
                }

                return Localizer::trans("list-info",
                        [
                            $faction->getName(),
                            count($faction->getOnlineMembers()),
                            count($faction->getMembers()),
                            $faction->getLandCount(),
                            ($p = $faction->getPower(true)) === PHP_INT_MAX ? Localizer::trans("infinity") : $p,
                            ($p = $faction->getPowerMax()) === PHP_INT_MAX ? Localizer::trans("infinity") : $p,
                        ]
                    ) . ($sender->isOp() ? ($faction->hasLandInflation() ? TextFormat::RED . " <LAND INFLATION>" : "") : "");
            }

        });
        $pager->stringify();
        $pager->sendTitle($sender);

        foreach ($pager->getOutput() as $line) {
            $sender->sendMessage($line);
        }

        return;
    }

}