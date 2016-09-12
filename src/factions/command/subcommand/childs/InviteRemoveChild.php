<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/16/16
 * Time: 7:20 PM
 */

namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\EvalCore;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class InviteRemoveChild extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "remove", "Delete an invitation", FactionsPE::INVITE_REMOVE, ["delete"]);

        $this->addParameter(new Parameter("player", new TypeString(), false));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) return true;

        $fsender = FPlayer::get($sender);
        $player = $this->getParameter("player")->getValue();
        if(($p = EvalCore::getPlayer($player)) instanceof Player) $player = $p->getName();
        $faction = $fsender->getFaction();
        if($faction->isInvited($player)) {
            $faction->setInvited($player, false);
            $sender->sendMessage(Text::parse("%var0 is no longer invited to %var1 faction", $player, $faction->getName()));
        } else {
            $sender->sendMessage(Text::parse("%var0 is not invited", $player));
        }
        return true;
    }

}