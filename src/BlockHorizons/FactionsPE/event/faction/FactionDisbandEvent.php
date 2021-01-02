<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event\faction;

use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\entity\IMember;
use pocketmine\event\Cancellable;

class FactionDisbandEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var IMember */
    public $member;

    /** @var int */
    public $reason;

    public function __construct(IMember $member, Faction $faction, $reason = Faction::DISBAND_REASON_UNKNOWN)
    {
        parent::__construct($faction);
        $this->member = $member;
        $this->reason = $reason;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getReason()
    {
        return $this->reason;
    }

}