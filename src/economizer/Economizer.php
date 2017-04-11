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
 
 namespace economizer;
 
use pocketmine\plugin\Plugin;

use economizer\transistor\EconomyAPI;
use economizer\transistor\PocketMoney;
use economizer\transistor\MassiveEconomy;
use economizer\transistor\EssentialsPE;

class Economizer {

	const ECONOMY_API		= "EconomyAPI";
	const POCKET_MONEY		= "PocketMoney";
	const MASSIVE_ECONOMY 		= "MassiveEconomy";
	const ESSENTIALSPE		= "EssentialsPE";
	const DEFAULT_API		= self::ECONOMY_API;

	/** @var Transistor */
	protected $transistor;

	/** @var Plugin */
	protected $main;

	public static $transistors = [
		self::ECONOMY_API		=> EconomyAPI::class,
		self::POCKET_MONEY 		=> PocketMoney::class,
		self::MASSIVE_ECONOMY 		=> MassiveEconomy::class,
		self::ESSENTIALSPE		=> EssentialsPE::class
	];

	public function __construct(Plugin $plugin, Transistor $transistor = null) {
		$this->main = $plugin;
		if($transistor !== null) $this->transistor = $transistor;
	}

	/**
	 * Returns true if transistor is attached to api plugin which is ready to serve
	 * @return bool
	 */
	public function ready() : bool {
		if(!$this->transistor) return false;
		return $this->transistor->ready();
	}

	public function setTransistor(Transistor $transistor) {
		$this->transistor = $transistor;
	} 

	/**
	 * @return Transistor|null
	 */
	public function getTransistor() {
		return $this->transistor;
	}

	/**
	 * Makes code shorter
	 */
	public function __call($method, $arguments) {
		if(!method_exists($this, $method)) {
			if(!$this->transistor) return;
			if(method_exists($this->transistor, $method)) {
				return call_user_func_array([$this->transistor, $method], $arguments);
			}
		}
	}

	public static function getTransistorFor(Plugin $plugin) {
		if(!isset(self::$transistors[$plugin->getName()])) return null;
		return new self::$transistors[$plugin->getName()]($plugin);
	}

}
