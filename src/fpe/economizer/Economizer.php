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
 
 namespace fpe\economizer;
 
use fpe\economizer\transistor\iEconomy;
use pocketmine\plugin\Plugin;

use fpe\economizer\transistor\EconomyAPI;
use fpe\economizer\transistor\PocketMoney;
use fpe\economizer\transistor\MassiveEconomy;
use fpe\economizer\transistor\EssentialsPE;

class Economizer {

	const ECONOMY_API		= "EconomyAPI";
	const POCKET_MONEY		= "PocketMoney";
	const MASSIVE_ECONOMY 	= "MassiveEconomy";
	const ESSENTIALSPE		= "EssentialsPE";
	const IECONOMY          = "iEconomy";
	const DEFAULT_API		= self::ECONOMY_API;
	
	/** @var Economizer */
	private static $instance;
	public static function get() { return self::$instance; }

	/** @var Transistor */
	protected $transistor;

	/** @var Plugin */
	protected $main;

	public static $transistors = [
		self::ECONOMY_API		=> EconomyAPI::class,
		self::POCKET_MONEY 		=> PocketMoney::class,
		self::MASSIVE_ECONOMY 	=> MassiveEconomy::class,
		self::ESSENTIALSPE		=> EssentialsPE::class,
        self::IECONOMY          => iEconomy::class
	];

	public function __construct(Plugin $plugin, Transistor $transistor = null) {
		$this->main = $plugin;
		if($transistor !== null) $this->transistor = $transistor;
		self::$instance = $this;
	}

	/**
	 * Returns true if transistor is attached to api plugin which is ready to serve
	 * @return bool
	 */
	public function ready() : bool {
		if($this->transistor === null) return false;
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
			if($this->transistor === null) return null;
			if(method_exists($this->transistor, $method)) {
				return call_user_func_array([$this->transistor, $method], $arguments);
			}
		}
		return null;
	}

	public static function getTransistorFor(Plugin $plugin) {
		if(!isset(self::$transistors[$plugin->getName()])) return null;
		return new self::$transistors[$plugin->getName()]($plugin);
	}

}

