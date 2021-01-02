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
 
namespace BlockHorizons\FactionsPE\economizer;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
 
abstract class Transistor {

	protected $api;

	public function __construct(Plugin $api) {
		$this->api = $api;
	}

	public function getAPI() : Plugin {
		return $this->api;
	}

	public abstract function ready() : bool;

	/**
	 * @param Player|string $player
	 * @return int|float
	 */
	public abstract function balance($player);

	/**
	 * @param Player|string $player
	 * @param int|float $money
	 * @param array $params = [] in case of other settings
	 */
	public abstract function setMoney($player, $money, array $params = []);

	/**
	 * @param Player|string $player
	 * @param int|float $money
	 * @param array $params = [] in case of other settings
	 */
	public abstract function addMoney($player, $money, array $params = []);

	/**
	 * @param Player|string $player
	 * @param int|float $money
	 * @param array $params = [] in case of other settings
	 */
	public abstract function takeMoney($player, $money, array $params = []);

	/**
	 * Name of api plugin
	 * @return string
	 */
	public function getName() : string {
		return $this->getAPI()->getName();
	}

    /**
     * Return symbol of the money
     * @return string
     */
	public abstract function getMoneyUnit() : string;

}
