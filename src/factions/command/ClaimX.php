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

namespace factions\command;

use dominate\Command;

use factions\command\parameter\FactionParameter;
use factions\manager\Members;
use factions\manager\Plots;

use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;

abstract class ClaimX extends Command {
 	
 	/** @var bool */
	private $claim = true;
	
	/** @var int */
	private $factionArgIndex = 0;
	
	public function setup() {
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
	}

	public function isClaim() : bool { 
		return $this->claim; 
	}

	public function setClaim(bool $claim) { 
		$this->claim = $claim;
		return $this; 
	}

	
	public function getFactionArgIndex() : int { 
		return $this->factionArgIndex; 
	}

	public function setFactionArgIndex(int $factionArgIndex) {
		$this->factionArgIndex = $factionArgIndex; 
	}

	// -------------------------------------------- //
	// CONSTRUCT
	// -------------------------------------------- //
	
	public function execute(CommandSender $sender, $label, array $args) : BOOL {
		if(!parent::execute($sender, $label, $args)) return false;
		$this->sender = $sender;
        
        // Args
        $newFaction = $this->getArgument($this->getFactionArgIndex());

		$plots = $this->getPlots($sender);
		// Apply / Inform

		if($this->claim) {
			Plots::tryClaim($newFaction, Members::get($sender), $plots);
		} else {
			foreach($plots as $plot) {
				$plot->unclaim();
			}
		}

		$this->sender = null;
		return true;
	}
	
	/**
	 * @param Position $pos
	 * @return \factions\entity\Plot[]
	 */
	public abstract function getPlots(Position $pos) : array;
	
	
}