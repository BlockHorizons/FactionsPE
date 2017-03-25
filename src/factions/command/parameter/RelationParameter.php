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
use factions\relation\Relation;
use pocketmine\command\CommandSender;

class RelationParameter extends Parameter
{

    const ALL = 0;
    const ONE = 1;
    const ANY = 2;
    const RANK = 3;
    const RELATION = 4;

    public function setup()
    {
        $this->ERROR_MESSAGES = [
            self::ALL => "type-rel-all",
            self::ANY => "type-rel-any",
            self::ONE => "type-rel-one",
            self::RANK => "type-rel-rank",
            self::RELATION => "type-rel"
        ];
    }

    public function read(string $input, CommandSender $sender = null)
    {
        $silent = $sender === null;
        if (strtolower($input) === "all") {
            $rel = Relation::getAll();
        } else {
            $rel = Relation::fromString($input);
        }
        return $rel;
    }

    public function isValid($value, CommandSender $sender = null): bool
    {
        if (!$value) return false;
        switch ($this->type) {
            case self::ALL:
                return is_array($value);
            case self::RANK:
                return Relation::isRankValid($value);
            case self::RELATION:
                return !Relation::isRankValid($value);
            case self::ANY:
                return Relation::isValid($value);
            case self::ONE:
                return !is_array($value) && Relation::isValid($value);
            default:
                return false;
        }
    }

}