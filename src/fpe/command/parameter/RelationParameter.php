<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command\parameter;

use fpe\dominate\parameter\Parameter;
use fpe\relation\Relation;
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
                return is_array($value) || Relation::isValid($value);
            case self::ONE:
                return !is_array($value) && Relation::isValid($value);
            default:
                return false;
        }
    }

}