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

use onebone\economyapi\EconomyAPI as EAPI;

use BlockHorizons\FactionsPE\economizer\Transistor;
 
class EconomyAPI extends Transistor {

	public function __construct(EAPI $api) {
		parent::__construct($api);
	}

	/**
	 * Get player money
	 * @param Player|string $player
	 * @return int
	 */
	public function balance($player) {
		return $this->getAPI()->myMoney($player);
	}

	/**
	 * Set player money.
	 * @param Player|string $player
	 * @param int $money
	 * @param array $params = [], accepts "force" boolean and "issuer" Player|string
	 */
	public function setMoney($player, $money, array $params = []) {
		$force = $params["force"] ?? false;
		$issuer = $params["issuer"] ?? null;
		return $this->getAPI()->setMoney($player, $money, $force, $issuer);
	}

	/**
	 * Add money player current balance.
	 * @param Player|string $player
	 * @param int $money
	 * @param array $params = [], accepts "force" boolean and "issuer" Player|string
	 */
	public function addMoney($player, $money, array $params = []) {
		$force = $params["force"] ?? false;
		$issuer = $params["issuer"] ?? null;
		return $this->getAPI()->addMoney($player, $money, $force, $issuer);
	}

	/**
	 * Take player money.
	 * @param Player|string $player
	 * @param int $money
	 * @param array $params = [], accepts "force" boolean and "issuer" Player|string
	 */
	public function takeMoney($player, $money, array $params = []) {
		$force = $params["force"] ?? false;
		$issuer = $params["issuer"] ?? null;
		return $this->getAPI()->reduceMoney($player, $money, $force, $issuer);
	}

	public function ready() : bool {
		if($this->getAPI() instanceof EAPI && $this->getAPI()->isEnabled()) return true;
		return false;
	}

	public function getMoneyUnit() : string {
        return $this->getAPI()->getMonetaryUnit();
    }
}
