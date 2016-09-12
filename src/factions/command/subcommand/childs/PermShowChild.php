<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/12/16
 * Time: 11:44 PM
 */

namespace factions\command\subcommand\childs;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use factions\command\parameter\type\TypeFaction;
use factions\command\parameter\type\TypePerm;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;

class PermShowChild extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "show", "See permitted relations for permission", FactionsPE::PERM_SHOW, ["sw"]);
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, "me"));
        $this->addParameter(new Parameter("perm", new TypePerm(), false, "all"));
        $this->addParameter(new Parameter("page", new TypeInteger(), false, 1));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) return true;

        /** @var Perm[] $perms */
        $perms = $this->getParameter("perm")->getValue();
        if(!is_array($perms)) $perms = [$perms];
        $faction = $this->getParameter("faction")->getValue();
        $perms = array_chunk($perms, 7);
        $pageNumber = (int)min(count($perms), $this->getParameter("page")->getValue());
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $messages = [];
        $messages[] = Text::titleize("Perm for ".$faction->getName()." (page $pageNumber of ".count($perms).")");
        $messages[] = Perm::getStateHeaders();
        foreach($perms[$pageNumber-1] as $perm) {
            /** @var Perm $perm */
            $messages[] = Text::parse($perm->getStateInfo($faction->getPermitted($perm), true));
        }
        $sender->sendMessage($messages);
        return true;
    }

}