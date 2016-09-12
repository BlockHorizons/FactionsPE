<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/15/16
 * Time: 12:24 PM
 */

namespace factions\command\subcommand\childs;


use factions\command\parameter\Parameter;
use factions\command\parameter\type\TypeFaction;
use factions\command\parameter\type\TypeString;
use factions\entity\Faction;
use factions\FactionsPE;
use pocketmine\level\Position;
use pocketmine\Player;

class ClaimCircleChildcommand extends ClaimXRadiusChildBase
{

    public function __construct(FactionsPE $plugin, bool $claim)
    {
        parent::__construct($plugin,
            "circle",
            "Claim plots in radius of circle",
            FactionsPE::CLAIM_CIRCLE,
            $claim,
            ["c"]);

        $this->getParameter("radius")->setDefaultValue("one");
    }

    public function getChunks() : ARRAY
    {
        // Common Startup
        /** @var Player $sender */
        $sender = $this->sender;
        $pos = $sender->getPosition();

        $chunk = new Position($pos->x >> 4, 0, $pos->z >> 4, $pos->level);

        $chunks = [];

		$chunks[] = $chunk; // The center should come first for pretty messages

		$radiusZero = $this->getRadiusZero();
		$radiusSquared = $radiusZero * $radiusZero;

		for ($dx = -$radiusZero; $dx <= $radiusZero; $dx++)
		{
            for ($dz = -$radiusZero; $dz <= $radiusZero; $dz++)
			{
                if ($dx*$dx + $dz*$dz > $radiusSquared) continue;

                $x = $chunk->x + $dx;
				$z = $chunk->z + $dz;

				$chunks[] = new Position($x, 0, $z, $chunk->level);
			}
		}

		return $chunks;
    }

}