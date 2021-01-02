<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command\parameter;

use BlockHorizons\FactionsPE\relation\Relation;
use pocketmine\command\CommandSender;

class RankParameter extends RelationParameter
{

    const NAMES_PROMOTE = [
        "promote",
        "plus",
        "+",
        "up"
    ];

    const NAMES_DEMOTE = [
        "demote",
        "minus",
        "-",
        "down"
    ];

    const REQUIRED_RANK = Relation::OFFICER;

    public static function isPromotion($value): bool
    {
        return in_array($value, self::NAMES_PROMOTE, true);
    }

    public static function isDemotion($value): bool
    {
        return in_array($value, self::NAMES_DEMOTE, true);
    }

    public function setup()
    {
        $this->ERROR_MESSAGE = "type-rank";
        $this->type = RelationParameter::RANK;
    }

    public function read(string $input, CommandSender $sender = null)
    {
        return strtolower($input);
    }

    public function isValid($value, CommandSender $sender = null): bool
    {
        if (in_array($value, array_merge(self::NAMES_DEMOTE, self::NAMES_PROMOTE, Relation::getAll()), true)) return $value;
        return parent::isValid($value, $sender);
    }

}
