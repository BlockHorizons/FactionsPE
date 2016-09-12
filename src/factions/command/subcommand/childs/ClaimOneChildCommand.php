<?php
namespace factions\command\subcommand\childs;

use evalcore\command\parameter\Parameter;
use factions\command\parameter\type\TypeFaction;
use factions\FactionsPE;
use pocketmine\level\Position;
use pocketmine\Player;

class ClaimOneChildCommand extends ClaimXChildBase
{

    /**
     * ClaimOneChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin, bool $claim)
    {
        parent::__construct($plugin, "one", "Claim one plot", FactionsPE::CLAIM_ONE, $claim, [1]);

        $this->addParameter(new Parameter("faction", new TypeFaction(), false, false, "me"));
    }

    public function getChunks() : ARRAY
    {
        /** @var Player $sender */
        $sender = $this->sender;
        $pos = $sender->getPosition();
        return [new Position($pos->x >> 4, 0, $pos->z >> 4, $pos->level)];
    }
    
}