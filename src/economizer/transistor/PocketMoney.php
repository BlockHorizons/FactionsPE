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
 
namespace economizer\transistor;

use pocketmine\Player;
use PocketMoney\PocketMoney as PMAPI;
use economizer\Transistor;
 
class PocketMoney extends Transistor {

	public function __construct(PMAPI $api) {
		parent::__construct($api);
	}
  	
  	/**
  	 * @param Player|string $player
  	 * @return int|false if account is not registered
  	 */
	public function balance($player) {
		return $this->getAPI()->getMoney($player instanceof Player ? $player->getName() : $player);
	}
  	
  	/**
  	 * @param Player|string $player
  	 * @param array $params = []
  	 * @return bool
  	 */
	public function setMoney($player, $money, array $params = []) {
		return $this->getAPI()->setMoney($player instanceof Player ? $player->getName() : $player, $money);
	}
  	
  	/**
  	 * @param Player|string $player
  	 * @param int $money
  	 * @param array $params = [] accepts "callEvent"
  	 * @return bool
  	 */
	public function addMoney($player, $money, array $params = []) {
	    if(isset($params["callEvent"])) {
	      return $this->getAPI()->grantMoney($player instanceof Player ? $player->getName() : $player, $money, (bool) $params["callEvent"]);
	    }
		return $this->getAPI()->grantMoney($player instanceof Player ? $player->getName() : $player, $money);
	}
  	
  	/**
  	 * @param Player|string $player
  	 * @param int $money
  	 * @param array $params = [] accepts "callEvent"
  	 * @return bool
  	 */
	public function takeMoney($player, $money, array $params = []) {
		if(isset($params["callEvent"])) {
      		return $this->getAPI()->grantMoney($player instanceof Player ? $player->getName() : $player, -$money, (bool) $params["callEvent"]);
    	}
		return $this->getAPI()->grantMoney($player instanceof Player ? $player->getName() : $player, -$money);
	}
  
	public function ready() : bool {
		if($this->getAPI() instanceof PMAPI && $this->getAPI()->isEnabled()) return true;
		return false;
	}

	public function getMoneyUnit(){
        return trim($this->getAPI()->getFormattedMoney(' '));
    }

}
