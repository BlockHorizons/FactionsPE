<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/12/16
 * Time: 10:12 PM
 */

namespace factions\command\subcommand\childs;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PermListChild extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        // Aliases
        parent::__construct($plugin, "list", "List available faction permissions",
            FactionsPE::PERM_LIST, ["l"]);

        // Parameters
        $this->addParameter(new Parameter("page", new TypeInteger(), false, 1));
    }

	public function execute(CommandSender $sender, $label, array $args) : bool
	{
        if(parent::execute($sender, $label, $args) === false) return true;

        /** @var Player $sender */
        $fsender = FPlayer::get($sender);
        // Args
        $page = $args[0];

		// Create messages
		$book = [];
		foreach (Perm::getAll() as $perm) {
            if (!$perm->isVisible() && !$fsender->isOverriding()) continue;
            $messages[] = $perm->getDesc();
        }
        $book = array_chunk($book, 5);
        $page = $page - 1 > ($max = count($book)) ? $max : $page;
        $page = $page < 1 ? 1 : $page;
        $header = Text::parse("command.perm.list.header", $page, $max);
		// Send messages
        $sender->sendMessage($header);
		$sender->sendMessage($book[$page]);
        return true;
	}


}