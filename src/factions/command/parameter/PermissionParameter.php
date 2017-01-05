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

namespace factions\command\parameter;

use dominate\parameter\Parameter;

use factions\permission\Permission;
use factions\manager\Permissions;
use factions\manager\Members;

use pocketmine\command\CommandSender;

class PermissionParameter extends Parameter {

	const ALL = 20;
	const ANY = 21;
	const ONE = 22;

    public function setup() {
        $this->ERROR_MESSAGES = [
            self::ALL => "type-perm-all",
            self::ANY => "type-perm-any",
            self::ONE => "type-perm-one"
        ];
    }

    public function read(string $input, CommandSender $sender = null) {
    	if(strtolower($input) === "all") {
    		return Permissions::getAll();
    	}
        $perm = Permissions::getById(strtolower($input));
        if($perm && !$perm->isVisible() && $sender) {
        	if(!Members::get($sender)->isOverriding()) {
        		$perm = null;
        	}
        }
        return $perm;
    }

    public function isValid($value, CommandSender $sender = null) : bool {
        switch($this->type) {
        	case self::ALL:
        		return is_array($value);
        	case self::ANY:
        		return $value !== null;
        	case self::ONE:
        		return $value instanceof Permission;
        	default:
        		return false;
        }
    }

}