<?php
/*
 *   Economizer: Economy library
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
namespace BlockHorizons\FactionsPE\economizer\transistor;
use pocketmine\Player;
use EssentialsPE\Loader;
use EssentialsPE\BaseFiles\BaseAPI;
use BlockHorizons\FactionsPE\economizer\Transistor;
 
class EssentialsPE extends Transistor {
  
  /**
   * @param Loader|BaseAPI
   */
	public function __construct($api) {
    if($api instanceof BaseAPI) {
      $api = $api->getEssentialsPEPlugin();
    } elseif (!($api instanceof Loader)) {
      throw new \InvalidArgumentException("Argument 1 passed to ".__CLASS__."::".__METHOD__." must be instanceof '".Loader::CLASS."' or '".BaseAPI::class."'");
    }
		parent::__construct($api);
	}
  
	/**
	 * Get player money
	 * @param Player $player
	 * @return int
	 */
	public function balance($player) {
		return BaseAPI::getInstance()->getPlayerBalance($player);
	}
  
	/**
	 * Set player money.
	 * @param Player $player
	 * @param int $money
	 */
	public function setMoney($player, $money, array $params = []) {
		return BaseAPI::getInstance()->setPlayerBalance($player, $money);
	}
  
	/**
	 * Add money player current balance.
	 * @param Player $player
	 * @param int $money
	 */
	public function addMoney($player, $money, array $params = []) {
		return BaseAPI::getInstance()->addToPlayerBalance($player, $money);
	}
  
	/**
	 * Take player money.
	 * @param Player $player
	 * @param int $money
	 */
	public function takeMoney($player, $money, array $params = []) {
		return BaseAPI::getInstance()->addToPlayerBalance($player, -$money);
	}
  
	public function ready() : bool {
		if($this->getAPI() instanceof Loader && $this->getAPI()->isEnabled()) return true;
		return false;
	}
  
  /**
   * @return string
   */
	public function getMoneyUnit(){
        return BaseAPI::getInstance()->getCurrencySymbol();
  }
    
}
