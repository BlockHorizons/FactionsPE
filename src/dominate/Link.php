<?php
/*
 *   Dominate: Advanced command library for PocketMine-MP
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

namespace dominate;

use pocketmine\command\CommandSender;

/**
 * Sometimes you want to make shortcuts for the commands eg. /f rank ChrisPrime promote => /f promote ChrisPrime
 * 
 * @author ChrisPrime
 */
class Link extends Command {

	/** @var Command */
	protected $target;

	/**
	 * How passed arguments should pair with target command parameters
	 */
	protected $pairs;


	public function __construct(Command $target, array $pairs) {
	    echo $target->getName().PHP_EOL;
	    return;
        $this->target = $target;
        $this->pairs = $pairs;
	}

	public function getUsage(CommandSender $sender = null) {
		$cmd = "/" . $this->target->getName();
		foreach($this->pairs as $local => $ext) {
		    $cmd .= " ".$this->getParameterAt($ext)->getTemplate($sender);
        }
	}

	public function execute(CommandSender $sender, $label, array $args) {
		return true;
	}

}