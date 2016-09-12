<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use factions\entity\Flag;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use factions\utils\Pager;
use evalcore\entity\CPlayer;

class TopSubCommand extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "top", "See top of most powerful factions", "factions.command.top");
        
        $this->addParameter(new Parameter("page", new TypeInteger(), false, false, 1));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if( parent::execute($sender, $label, $args) === false) return true;

        $height = 5;
        if($sender instanceof ConsoleCommandSender) $height = 20;

        $factions = Factions::getAll();
        $order = [];
        $keys = [];
        $book = [];
        foreach($factions as $faction) {
            if ($faction->getFlag(Flag::INFINITY_POWER) || $faction->isNone() or $faction->isSpecial()) {
                continue;
            }
            $order[$faction->getName()] = $faction->getPower();
            $keys[] = $faction->getName();
        }
        arsort($order);
        foreach($order as $key => $power) {
            $book[$key] = [$key, $power];
        }

        if(empty($book)) {
            $sender->sendMessage(Text::parse("command.top.empty"));
            return true;
        }
        $page = $this->getParameter("page")->getValue();
        
        $pager = new Pager("command.top.header", $page, 5, $book, function(array $d, int $i, CommandSender $sender) {
            return Text::parse('command.top.line', $i+1, $d[0], $d[1]);
        }, $sender);
        $pager->stringify();

        $sender->sendMessage(Text::titleize(Text::parse($pager->getHeader(), $pager->getPage(), $pager->getMax())));
        if($sender instanceof CPlayer)
            $sender->sendMessage($pager->getOutput());
        else
            foreach($pager->getOutput() as $l) $sender->sendMessage($l);
        
        return true;
    }

}