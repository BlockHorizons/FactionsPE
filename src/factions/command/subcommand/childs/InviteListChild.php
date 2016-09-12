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
use evalcore\command\parameter\type\primitive\TypeInteger;
use evalcore\entity\CPlayer;
use factions\command\parameter\type\TypeFaction;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\objs\Rel;
use factions\utils\Pager;
use factions\utils\Text;
use pocketmine\command\CommandSender;

class InviteListChild extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "list", "List all active invitations for faction", FactionsPE::INVITE_LIST, ["ls"]);

        $this->addParameter(new Parameter("page", new TypeInteger(), false, 1));
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, "me"));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) return true;

        // Args
        $fsender = FPlayer::get($sender);
        $page = $this->getParameter("page")->getValue();
        /** @var Faction $faction */
		$faction = $this->getParameter("faction")->getValue();
		
		if ( $faction !== $fsender->getFaction() && ! $sender->hasPermission(FactionsPE::INVITE_LIST_OTHER)) return false;
		
		// MPerm
		if ( ($perm = Perm::getPermById(Perm::INVITE)) && !$perm->has($fsender, $faction)) {
            $sender->sendMessage(Text::parse("<red>You don't have permission to ".$perm->getDescription()));
            return false;
        }
		
		// Pager Create
		$players = $faction->getInvitedPlayers();
		$pager = new Pager(Text::titleize("Invited Players List"), $page, 5, $players, $stringifier = function($player, int $index, CommandSender $sender){
                if(($target = FPlayer::get($player))) {
                    $targetName = $target->getDisplayName();
                    $isAre = "is";
                    $targetRank = $target->getRole();
                    $targetFaction = $target->getFaction();
                    $theAan = $targetRank === Rel::LEADER ? "the" : Text::aan($targetRank);
                    $rankName = strtolower(Text::getNicedEnum($targetRank));
                    $ofIn = $targetRank === Rel::LEADER ? "of" : "in";
                    $factionName = $targetFaction->getName();
                    return Text::parse("%var0 <i>%var1 %var2 <h>%var3 <i>%var4 %var5<i>.", $targetName, $isAre, $theAan, $rankName, $ofIn, $factionName);
                } else {
                    return Text::parse("%var0", $player);
                }
        }, $sender);
        $pager->stringify();
		
		// Pager Message
        $sender->sendMessage($pager->getHeader());
		if($sender instanceof CPlayer) {
            $sender->sendMessage($pager->getOutput());
            var_dump($pager->getOutput());
        } else {
            foreach($pager->getOutput() as $l) $sender->sendMessage($l);
        }
        return true;
    }

}