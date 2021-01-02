<?php

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\flag\Flag;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Factions;
use BlockHorizons\FactionsPE\utils\Pager;
use BlockHorizons\FactionsPE\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class Top extends Command
{

    public function setup()
    {
        $this->addParameter((new Parameter("power|online"))->setDefaultValue("power"));
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $height = 5;
        $sortBy = $this->getArgument(0);
        if ($sender instanceof ConsoleCommandSender) {
            $height = 20;
        }

        $factions = Factions::getAll();
        $order = [];
        $keys = [];
        $book = [];

        if ($sortBy === "power") {
            foreach ($factions as $faction) {
                if ($faction->getFlag(Flag::INFINITY_POWER) || $faction->isNone() || $faction->isSpecial()) {
                    continue;
                }
                $order[$faction->getName()] = $faction->getPower();
            }
        } elseif ($sortBy === "online") {
            foreach ($factions as $faction) {
                if ($faction->getTimeJoined() === null) {
                    continue;
                }
                $order[$faction->getName()] = $faction->getOnlineTime();
            }
        } else {
            $sender->sendMessage(Localizer::translatable("unknown-sort-by", ['sort' => $sortBy]));
            return "sort-by-tip";
        }

        // Sort
        asort($order);
        $book = array_reverse($order);
        foreach ($book as $key => $value) {
            $book[$key] = [$key, $value];
        }

        if (empty($book)) {
            $sender->sendMessage(Localizer::translatable("top-empty"));
            return true;
        }
        $page = $this->getArgument(1);

        $pager = new Pager("top-header", $page, $height, $book, $sender, function (array $d, int $i, CommandSender $sender) use ($height, $page, $sortBy) {
            $value = $sortBy === "power" ? $d[1] : Text::time_elapsed($d[1], true);
            return Localizer::trans('top-line', [($page - 1) * $height + $i + 1, $d[0], $value]);
        });
        $pager->stringify();
        $pager->sendTitle($sender, ["sort-by" => $sortBy]);

        foreach ($pager->getOutput() as $l) {
            $sender->sendMessage($l);
        }

        return true;
    }
}