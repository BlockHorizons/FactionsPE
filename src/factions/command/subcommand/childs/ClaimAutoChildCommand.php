<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/14/16
 * Time: 11:20 PM
 */

namespace factions\command\subcommand\childs;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use factions\command\parameter\type\TypeFaction;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\objs\Plots;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;

class ClaimAutoChildCommand extends Command
{

    /**
     * ClaimAutoChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "auto", "Set auto claiming", "factions.command.claim.auto", ["a"]);

        $this->addParameter(new Parameter("faction", new TypeFaction(), false, "me"));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if( parent::execute($sender, $label, $args) === false) return true;
        /** @var Player $sender */
        $fsender = FPlayer::get($sender);
		$newFaction = $fsender->getFaction();
		
		// Disable?
		if ($newFaction == null || $newFaction == $fsender->getAutoClaimFaction())
        {
            $fsender->setAutoClaimFaction(null);
            $fsender->sendMessage(Text::parse("<i>Disabled auto-setting as you walk around."));
            return true;
        }
		
		// MPerm Preemptive Check
		if ($newFaction->isNormal() && ! Perm::getPermTerritory()->has($fsender, $newFaction)) return true;
		
		// Apply / Inform
		$fsender->setAutoClaimFaction($newFaction);
		$sender->sendMessage(Text::parse("<i>Now auto-setting <h>%s<i> land.", $newFaction->getName()));
		
		// Chunks
		$chunk = new Position($sender->getX() >> 4, 0, $sender->getY() >> 4, $sender->getLevel());
		
		// Apply / Inform
		Plots::get()->tryClaim($newFaction, $fsender, [$chunk]);

        return true;
    }
}