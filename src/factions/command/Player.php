<?php
namespace factions\command;

use dominate\Command;
use facitons\manager\Members;
use factions\command\parameter\MemberParameter;
use factions\interfaces\IFPlayer;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Player extends Command
{

    public function setup()
    {
        $this->addParameter((new MemberParameter("player", MemberParameter::ANY_MEMBER))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $member = $this->getArgument(0);

        // INFO: Power (as progress bar)
        $progressbarQuota = 0;
        $playerPowerMax = $member->getPowerMax();

        if ($playerPowerMax != 0) {
            $progressbarQuota = $member->getPower() / $playerPowerMax;
        }

        # TODO: Calculate progress bar width
        //$sender->sendMessage(Localizer::translatable("player-power-progress-bar", [(new ProgressBar(ProgressBar::HEALTH_BAR_CLASSIC, $progressbarQuota, 10))->setColor(TextFormat::DARK_PURPLE)->render()]));
        // INFO: Power (as digits)
        $sender->sendMessage(Localizer::translatable("player-power", [$member->getPower(), $member->getPowerMax()]));
        $sender->sendMessage(Text::parse("<gold>Rank: <h>" . $member->getRole()));
        $sender->sendMessage(Text::parse("<gold>Faction: <h>" . ($member->hasFaction() ? $member->getFaction()->getName() : "none")));
        $sender->sendMessage(Text::parse("<gold>Last online: <h>" . Text::ago($member->getLastPlayed())));

        // INFO: Power Boost
        if ($member->hasPowerBoost()) {
            $powerBoost = $member->getPowerBoost();
            $powerBoostType = ($powerBoost > 0 ? Localizer::translatable("bonus") : Localizer::translatable("penalty"));
            $sender->sendMessage(Localizer::translatable("player-power-boost", [$powerBoost, $powerBoostType]));
        }
        return true;
    }
}