<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/20/16
 * Time: 1:06 AM
 */

namespace factions\command\subcommand;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\entity\CPlayer;
use factions\command\FactionCommand;
use factions\FactionsPE;
use factions\utils\Pager;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HelpSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "help", "Show FactionsPE command help page", "factions.help", ["?"]
        );
        # addRequirement: args

        $this->addParameter(new Parameter("page|command", new TypeString(), false, false, 1));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

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

        /** @var FactionCommand $main */
        $main = $sender->getServer()->getCommandMap()->getCommand("faction");
        if ($command === "") {
            /** @var Command[][] $commands */
            $commands = [];
            /** @var Command $command */
            foreach ($main->getChilds() as $command) {
                if ($command->testPermissionSilent($sender)) {
                    $commands[$command->getName()] = $command;
                }
            }
            ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
            $pager = new Pager("command.help.header", $pageNumber, $pageHeight, $commands, $stringifier = function(Command $cmd, int $index, CommandSender $sender){
                return Text::parse('command.help.line', TextFormat::DARK_GREEN, $cmd->getName(), $cmd->getDescription());
            }, $sender);
            $pager->stringify();

            $sender->sendMessage(Text::parse($pager->getHeader(), $pager->getPage(), $pager->getMax()));
            if($sender instanceof CPlayer) {
                $sender->sendMessage($pager->getOutput());
            } else {
                foreach($pager->getOutput() as $l) $sender->sendMessage($l);
            }

            return true;
        } else {
            $w = explode(" ", $command);
            $cmd = null;
            $i = 0;
            do {
                echo $w[$i]."\n";
                if($i === 0) $cmd = $main->getChild($w[$i]);
                else $cmd = $cmd->getChild($w[$i]);
                $i ++;
            } while($cmd instanceof Command and $cmd->isParent() and isset($w[$i]));
            if($cmd instanceof Command) {
                if ($cmd->testPermissionSilent($sender)) {
                    $message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: /f " . $cmd->getName() . TextFormat::YELLOW . " ---------\n";
                    $message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $cmd->getDescription() . "\n";
                    $message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $cmd->getUsage($sender) . "\n";
                    $sender->sendMessage($message);

                    return true;
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "No help for " . strtolower($command));
            }

            return true;
        }
    }


}