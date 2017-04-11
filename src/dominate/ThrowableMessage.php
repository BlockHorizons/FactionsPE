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

use localizer\Translatable;
use localizer\Localizer;

class ThrowableMessage extends \Exception {

	/** @var string|Translatable */
	public $message;

    /**
     * @param string $message
     * @internal param $ string|Translatable
     */
	public function __construct($message) {
		if(!$message instanceof Translatable) {
			if(($nm = Localizer::translatable($message))->getText() !== $message) {
				$message = $nm;
			}
		}
		$this->message = $message;
	}

	public function getText() {
		return $this->message;
	}

}