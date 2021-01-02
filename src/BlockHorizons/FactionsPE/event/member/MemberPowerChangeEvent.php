<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event\member;

use BlockHorizons\FactionsPE\entity\IMember;
use pocketmine\event\Cancellable;

class MemberPowerChangeEvent extends MemberEvent implements Cancellable
{

    const CUSTOM = 0x0;
    const DEATH = 0x1;
    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;
    /** @var int */
    protected $newPower, $reason;

    public function __construct(IMember $member, int $newPower, $reason = self::CUSTOM)
    {
        parent::__construct($member);
        $this->newPower = $newPower;
        $this->reason = $reason;
    }

    public function getNewPower()
    {
        return $this->newPower;
    }

    public function getReason()
    {
        return $this->reason;
    }

}