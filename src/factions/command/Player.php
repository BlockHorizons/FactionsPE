<?php
namespace factions\command;

use dominate\Command;

use factions\command\parameter\MemberParameter;
use factions\FactionsPE;
use factions\entity\IMember;
use facitons\manager\Members;
use factions\interfaces\IFPlayer;
use factions\utils\ProgressBar;
use factions\utils\Text;

use pocketmine\Player as PPlayer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use localizer\Localizer;

class Player extends Command {

    public function setup() {
        $this->addParameter((new MemberParameter("player", MemberParameter::ANY_MEMBER))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args) {
        $member = $this->getArgument(0);

        // INFO: Power (as progress bar)
        $progressbarQuota = 0;
		$playerPowerMax = $member->getPowerMax();

		if ($playerPowerMax != 0) {
            $progressbarQuota = $member->getPower() / $playerPowerMax;
        }

        # TODO: Calculate progress bar width
		$sender->sendMessage(Localizer::translatable("player-power-progress-bar", [(new ProgressBar(ProgressBar::HEALTH_BAR_CLASSIC, $progressbarQuota, 10))->setColor(TextFormat::DARK_PURPLE)->render()]));
		// INFO: Power (as digits)
		$sender->sendMessage(Localizer::translatable("player-power", [$member->getPower(), $member->getPowerMax()]));

		// INFO: Power Boost
		if ($member->hasPowerBoost()) {
            $powerBoost = $member->getPowerBoost();
			$powerBoostType = ($powerBoost > 0 ? Localizer::translatable("bonus") : Localizer::translatable("penalty"));
			$sender->sendMessage(Localizer::translatable("player-power-boost", [$powerBoost, $powerBoostType]));
		}
        return true;
    }
}