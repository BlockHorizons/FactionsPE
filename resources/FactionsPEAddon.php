<?php
declare(strict_types = 1);

/**
 * @name FactionsPEAddon
 * @version 1.0.0
 * @main BlockHorizons\FactionsPE\FactionsPEAddon
 * @depend FactionsPE
 */
namespace BlockHorizons\FactionsPE
{

	use JackMD\ScoreHud\addon\AddonBase;
	use pocketmine\Player;
	use BlockHorizons\FactionsPE\manager\Members;

	class FactionsPEAddon extends AddonBase {

		/**
		 * @param Player $player
		 * @return array
		 */
		public function getProcessedTags(Player $player): array{
			return [
				"{faction}"       => $this->getPlayerFaction($player),
				"{faction_power}" => $this->getFactionPower($player),
			];
		}

		/**
		 * @param Player $player
		 * @return string
		 */
		public function getPlayerFaction(Player $player): string{
			$member = Members::get($player);
			
			return $member->hasFaction() ? $member->getFaction()->getName() : "No faction";
		}

		/**
		 * @param Player $player
		 * @return string
		 */
		public function getFactionPower(Player $player){
			$member = Members::get($player);
			
			return $member->hasFaction() ? $member->getFaction()->getPower() : "No faction";
		}
	}
}