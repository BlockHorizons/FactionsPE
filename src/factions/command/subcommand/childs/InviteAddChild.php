<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/16/16
 * Time: 7:19 PM
 */

namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\EvalCore;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class InviteAddChild extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "add", "Add player to invitation list", FactionsPE::INVITE_LIST);

        $this->addParameter(new Parameter("player", new TypeString(), false));
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
    }

    public function execute(CommandSender $sender, $label, array $args) : bool {
        if(parent::execute($sender, $label, $args) === false) return true;
        /** @var Player $sender */
        $player = $this->getParameter("player")->getValue();
        $faction = FPlayer::get($sender)->getFaction();
        if($faction->isInvited($faction)) {
            $sender->sendMessage(Text::parse("%var0 is already invited", $player));
            return true;
        }
        $fplayer = FPlayer::get($player);
        if($fplayer->hasFaction()) {
            $sender->sendMessage(Text::parse("%var0 already has a faction", $player));
            return true;
        }
        if(($p = EvalCore::getPlayer($player))) $player = $p->getName();
        $faction->setInvited($player, true);
        $sender->sendMessage(Text::parse('command.invite.success', $player));
        if($p !== NULL) $p->sendMessage(Text::parse('command.invite.inform.target', $sender->getDisplayName(), $faction->getName()));
        return true;
    }

}