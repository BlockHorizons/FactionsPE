<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command\requirement;

use fpe\dominate\requirement\SimpleRequirement;
use fpe\manager\Members;
use pocketmine\command\CommandSender;

class FactionRequirement extends SimpleRequirement
{

    const IN_FACTION = 7;
    const OUT_FACTION = 8;

    public static function onClassLoaded()
    {
        SimpleRequirement::$ERROR_MESSAGES[self::IN_FACTION] = "requirement.in-faction-error";
        SimpleRequirement::$ERROR_MESSAGES[self::OUT_FACTION] = "requirement.out-faction-error";
    }

    public function hasMet(CommandSender $sender, $silent = false): bool
    {
        $r = call_user_func(function () use ($sender) {
            switch ($this->type) {
                case self::IN_FACTION:
                    return (Members::get($sender))->hasFaction();
                case self::OUT_FACTION:
                    return !(Members::get($sender))->hasFaction();
            }
            return false;
        });
        if (!$r && !$silent) {
            $sender->sendMessage($this->createErrorMessage($sender));
        }
        return $r;
    }

}