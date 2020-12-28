<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\utils\Pager;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Help extends Command
{

    public function setup()
    {
        $this->addParameter((new Parameter("page|command"))->setDefaultValue(1));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (count($args) === 0) {
            $command = "";
            $pageNumber = 1;
        } elseif (is_numeric($args[count($args) - 1])) {
            $pageNumber = (int)array_pop($args);
            if ($pageNumber <= 0) {
                $pageNumber = 1;
            }
            $command = implode(" ", $args);
        } else {
            $command = implode(" ", $args);
            $pageNumber = 1;
        }
        if ($sender instanceof Player === false) {
            $pageHeight = PHP_INT_MAX;
        } else {
            $pageHeight = 5;
        }

        $main = $sender->getServer()->getCommandMap()->getCommand("faction");
        if ($command === "") {

            $commands = [];
            foreach ($main->getChilds() as $command) {
                if ($command->testPermissionSilent($sender)) {
                    $commands[$command->getName()] = $command;
                }
            }

            ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
            $pager = new Pager("help-header", $pageNumber, $pageHeight, $commands, $sender, function (Command $cmd, int $index, CommandSender $sender) {
                return Localizer::trans('help-line', ["color" => "<green>", "name" => $cmd->getName(), "desc" => $cmd->getDescription()]);
            });
            $pager->stringify();

            $pager->sendTitle($sender);

            foreach ($pager->getOutput() as $l) {
                $sender->sendMessage($l);
            }

            return true;
        } else {
            $w = explode(" ", $command);
            $cmd = null;
            $i = 0;
            do {
                if ($i === 0) {
                    $cmd = $main->getChild($w[$i]);
                } else {
                    $cmd = $cmd->getChild($w[$i]);
                }

                $i++;
            } while ($cmd instanceof Command and $cmd->isParent() and isset($w[$i]));
            if ($cmd instanceof Command) {
                if ($cmd->testPermissionSilent($sender)) {
                    $message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: " . $cmd->getName() . TextFormat::YELLOW . " ---------\n";
                    $message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $cmd->getDescription() . "\n";
                    $message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $cmd->getUsage() . "\n";
                    $sender->sendMessage($message);
                    return true;
                }
            } else {
                $sender->sendMessage(Localizer::translatable("no-help-for-command", [
                    "command" => $command,
                ]));
            }
            return true;
        }
    }

}