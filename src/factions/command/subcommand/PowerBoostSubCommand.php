<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use evalcore\command\parameter\type\primitive\TypeString;
use factions\objs\Factions;
use factions\entity\FPlayer;
use factions\utils\Text;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class PowerBoostSubCommand extends Command
{
	
	public function __construct(FactionsPE $plugin)
	{
	    parent::__construct($plugin, "powerboost", "Give power boost to faction or player", FactionsPE::POWERBOOST, ["pb"]);

		// Parameters
		$this->addParameter(new Parameter("p|f", new TypeString(), false, true));
		$this->addParameter(new Parameter("player|faction", new TypeString(), false, true));
		$this->addParameter(new Parameter("boost", new TypeInteger(), false, true));
	}

	public function execute(CommandSender $sender, $label, array $args) : bool
	{
	    if(parent::execute($sender, $label, $args) === false) return true;
	    
	    $type = $this->getParameter("p|f")->getValue();
		$needle = $this->getParameter("player|faction")->getValue();
		if(strtolower($type) === "p" or strtolower($type) === "player") {
		    $doPlayer = true;
		} elseif (strtolower($type) === "f" or strtolower($type) === "faction") {
			$doPlayer = false;
		} else { // It's not :D let's continue alrighty so I leave it there
		    $sender->sendMessage(Text::parse("<b>You must specify \"p\" or \"player\" to target a player or \"f\" or \"faction\" to target a faction."));
			$sender->sendMessage(Text::parse("<b>ex. /f powerboost p SomePlayer 0.5  -or-  /f powerboost f SomeFaction -5"));
			return false;
		}
		
		$targetPower = $this->getParameter("boost")->getValue();

		$target = "";

		if ($doPlayer)
		{
		    if(!($player = $this->getPlugin()->getServer()->getPlayerExact($needle))) { // TODO# Some of offline instances are saved!
		        $sender->sendMessage(Text::parse("<red>That player couldn't be found!"));
		        return false;
		    }
		    $targetPlayer = FPlayer::get($player);
			$targetPlayer->setPowerBoost( (float) $targetPower);
			$target = "Player \"". $targetPlayer->getName() ."\"";
		}
		else
		{
		    if(!($faction = Factions::getByName($needle))) {
		        $sender->sendMessage(Text::parse("<red>That faction could't be found!"));
		        return false;
		    }
			$faction->setPowerBoost( (float) $targetPower);
			$target = "Faction \"" . $faction->getName()."\"";
		}

		$sender->sendMessage(Text::parse("<i>".$target." now has a power bonus/penalty of ".$targetPower." to min and max power levels."));
		FactionsPE::get()->getLogger()->info($sender->getName()." has set the power bonus/penalty for ".$target." to ".$targetPower.".");
	    return true;
	}
	
}