<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event\member;

use BlockHorizons\FactionsPE\entity\IMember;
use pocketmine\event\Cancellable;
use pocketmine\level\Position;

class MemberHomeTeleportEvent extends MemberEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var Position */
    public $home;

    public function __construct(IMember $member, Position $home)
    {
        parent::__construct($member);
        $this->home = $home;
    }

    public function getDestination()
    {
        return $this->home;
    }

    public function setDestination(Position $home)
    {
        $this->home = $home;
    }

    public function getMember()
    {
        return $this->member;
    }

}