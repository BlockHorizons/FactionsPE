<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
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

namespace factions\engine;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginLogger;

use factions\FactionsPE;

/**
 * Engines are listeners which control behaviour of the plugin by communicating
 * with back-end
 */
abstract class Engine implements Listener {

	protected $main;

	public function __construct(FactionsPE $main) {
		$this->main = $main;
	}

	public function getMain() : FactionsPE {
		return $this->main;
	}

	public function getLogger() : PluginLogger {
		return $this->getMain()->getLogger();
	}

}
