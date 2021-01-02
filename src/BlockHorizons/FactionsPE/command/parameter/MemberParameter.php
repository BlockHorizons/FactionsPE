<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command\parameter;

use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\entity\FConsole;
use BlockHorizons\FactionsPE\entity\IMember;
use BlockHorizons\FactionsPE\entity\Member;
use BlockHorizons\FactionsPE\entity\OfflineMember;
use BlockHorizons\FactionsPE\manager\Members;
use pocketmine\command\CommandSender;

class MemberParameter extends Parameter
{

    const ONLINE_MEMBER = 0;
    const OFFLINE_MEMBER = 1;
    const CONSOLE_MEMBER = 2;
    const ANY_MEMBER = 3;

    public function setup()
    {
        $this->ERROR_MESSAGES = [
            self::ONLINE_MEMBER => "type-member",
            self::OFFLINE_MEMBER => "type-member",
            self::CONSOLE_MEMBER => "type-console-member",
            self::ANY_MEMBER => "type-any-member",
        ];
    }

    /**
     * @param string $input
     * @param CommandSender|null $sender
     * @return mixed
     */
    public function read(string $input, CommandSender $sender = null)
    {
        if (($input === "me" || $input === "self") && $sender) {
            $member = Members::get($sender, true);
        } else {
            $member = Members::get($input, false);
        }
        return $member;
    }

    public function isValid($value, CommandSender $sender = null): bool
    {
        if ($value === null) return false;
        switch ($this->type) {
            case self::ONLINE_MEMBER:
                return $value instanceof Member && $value->isOnline();
            case self::OFFLINE_MEMBER:
                return $value instanceof OfflineMember && $value->isOnline() === false;
            case self::CONSOLE_MEMBER:
                return $value instanceof FConsole;
            case self::ANY_MEMBER:
                return $value instanceof IMember;
            default:
                return false;
        }
    }

}