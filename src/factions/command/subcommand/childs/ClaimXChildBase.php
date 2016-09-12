<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/14/16
 * Time: 10:10 PM
 */

namespace factions\command\subcommand\childs;

use evalcore\command\Command;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\objs\Plots;
use pocketmine\command\CommandSender;
use pocketmine\Player;

abstract class ClaimXChildBase extends Command
{
 
	private $claim = true;
	public function isClaim() : BOOL { return $this->claim; }
	public function setClaim(bool $claim) { $this->claim = $claim; }

	private $factionArgIndex = 1;
	public function getFactionArgIndex() : INT { return $this->factionArgIndex; }
	public function setFactionArgIndex(int $factionArgIndex) { $this->factionArgIndex = $factionArgIndex; }

	// -------------------------------------------- //
	// CONSTRUCT
	// -------------------------------------------- //

	public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, bool $claim, $aliases = [], $requirements = [])
	{
		parent::__construct($plugin, $name, $description, $permission, $aliases, $requirements);
        $this->setClaim($claim);

    }

	public function execute(CommandSender $sender, $label, array $args) : BOOL
	{
		if(parent::execute($sender, $label, $args) === false){
			return true;                                                                                                            
		}
        // Args
        $newFaction = $this->getParameter("faction")->getValue();
		$chunks = $this->getChunks();

		// Apply / Inform
		/** @var Player $sender */
		if($this->claim) {
			Plots::get()->tryClaim($newFaction, FPlayer::get($sender), $chunks);
		} else {
			foreach($chunks as $chunk) {
				Plots::get()->unclaim($chunk, false, true);
			}
		}
		return true;
	}

	// -------------------------------------------- //
	// ABSTRACT
	// -------------------------------------------- //

	public abstract function getChunks() : ARRAY;

	// -------------------------------------------- //
	// EXTRAS
	// -------------------------------------------- //
	
}