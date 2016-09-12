<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/15/16
 * Time: 11:04 AM
 */

namespace factions\command\subcommand\childs;


use factions\FactionsPE;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;

class ClaimSquareChildCommand extends ClaimXRadiusChildBase
{

    protected $radius = 0;

    /**
     * ClaimSquareChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin, bool $claim)
    {
        parent::__construct($plugin,
            "square",
            "Claim a square",
            FactionsPE::CLAIM_SQUARE,
            $claim,
            ["s"],
            []);
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

		for ($dx = -$radiusZero; $dx <= $radiusZero; $dx++)
		{
            for ($dz = -$radiusZero; $dz <= $radiusZero; $dz++)
			{
                $x = $chunk->x + $dx;
				$z = $chunk->z + $dz;
				
				$chunks[] = new Position($x, 0, $z, $pos->level);
			}
		}
		
		return $chunks;
    }
    
    public function execute(CommandSender $sender, $label, array $args) : BOOL {
        if (parent::execute($sender, $label, $args) === false) {
            echo "FAILED!";
            return true;
        }
        
        return true;
    }
}