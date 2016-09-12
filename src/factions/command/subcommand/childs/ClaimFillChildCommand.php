<?php
namespace factions\command\subcommand\childs;

use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use factions\command\parameter\type\TypeFaction;
use factions\entity\Faction;
use factions\FactionsPE;
use factions\objs\Plots;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\level\Position;
use pocketmine\Player;

class ClaimFillChildCommand extends ClaimXChildBase
{

    /**
     * ClaimFillChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin, bool $claim)
    {
        parent::__construct($plugin,
            "fill",
            "Claim filled area?!?",
            FactionsPE::CLAIM_FILL,
            $claim,
            ["f"]);

        $this->addParameter(new Parameter("radius", new TypeInteger(), false, 2));
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, "me"));
    }

    public function getChunks() : ARRAY
    {
        /** @var Player $sender */
        $sender = $this->sender;
        $pos = $sender->getPosition();
        $pos = new Position($pos->x >> 4, 0, $pos->z >> 4, $pos->level);
		$chunks = [];

		// What faction (aka color) resides there?
		// NOTE: Wilderness/None is valid.
		$color = Plots::get()->getFactionAt($sender);

		// We start where we are!
		$chunks[] = $pos;

		// Flood!
		$max = Settings::get("setFillMax", 5);
		$this->floodSearch($chunks, $color, $max);

		// Limit Reached?
		if (count($chunks) > $max)
        {
            $this->sender->sendMessage(Text::parse("<b>Fill limit of <h>%d <b>reached.", $max));
            return null;
        }

		// OK!
		return $chunks;
    }

    private function floodSearch(array &$chunks, Faction $color, int $max)
    {
        // Expand
      $expansion = [];
        /** @var Position $chunk */
        foreach ($chunks as $chunk)
		{
            $neighbours = [
                $chunk->add(1),
                $chunk->subtract(1),
                $chunk->add(0, 0, 1),
                $chunk->subtract(0, 0, -1)
            ];
			
			foreach ($neighbours as $neighbour)
			{
                if (in_array($neighbour, $chunks, true)) continue;
                $faction = Plots::get()->getFactionAt($neighbour, true);
				if ($faction == null) continue;
				if ($faction !== $color) continue;
				$expansion[] = $neighbour;
			}
		}
		array_merge($chunks, $expansion);
		
		// No Expansion?
		if (empty($expansion)) return;
		
		// Reached Max?
		if (count($chunks) >= $max) return;
		
		// Recurse
		$this->floodSearch($chunks, $color, $max);
    }
}