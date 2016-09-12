<?php
namespace factions\command\subcommand;


use evalcore\command\Command;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\utils\ProgressBar;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PlayerSubCommand extends Command
{


    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "player", "See more detailed info about someone", "factions.command.player", ["p"]);
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOL
    {
        if (parent::execute($sender, $label, $args) == false) {
            return true;
        }

        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                $fplayer = FPlayer::get($sender);
            } else {
                if (!($fplayer = FPlayer::getByName($args[0])) instanceof IFPlayer) {
                    $sender->sendMessage("Player not found!");
                    return true;
                }
            }
        } else {
            if (!isset($args[0])) {
                $this->sendUsage($sender);
                return true;
            } else {
                if (!($fplayer = FPlayer::getByName($args[0])) instanceof IFPlayer) {
                    $sender->sendMessage("Player not found!");
                    return true;
                }
            }
        }

        // INFO: Power (as progress bar)
        $progressbarQuota = 0;
		$playerPowerMax = $fplayer->getPowerMax();
		if ($playerPowerMax != 0)
        {
            $progressbarQuota = $fplayer->getPower() / $playerPowerMax;
        }

		$progressbarWidth = (int) round($fplayer->getPowerMax() / $fplayer->getPowerMaxUniversal() * 100);
		$sender->sendMessage(Text::parse("<a>Power: <v>%var0", (new ProgressBar(ProgressBar::HEALTH_BAR_CLASSIC, $progressbarQuota, $progressbarWidth))->render()));

		// INFO: Power (as digits)
		$sender->sendMessage(Text::parse("<a>Power: <v>%var0.2f / %var1 .2f", $fplayer->getPower(), $fplayer->getPowerMax()));

		// INFO: Power Boost
		if ($fplayer->hasPowerBoost())
        {
            $powerBoost = $fplayer->getPowerBoost();
			$powerBoostType = ($powerBoost > 0 ? "bonus" : "penalty");
			$sender->sendMessage(Text::parse("<a>Power Boost: <v>%var0 <i>(a manually granted %var1)", $powerBoost, $powerBoostType));
		}


        return true;
    }
}