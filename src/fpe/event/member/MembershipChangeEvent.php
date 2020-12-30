<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\event\member;

use fpe\entity\Faction;
use fpe\entity\IMember;
use LogicException;
use pocketmine\event\Cancellable;

/**
 * Class MembershipChangeEvent
 *
 * Called whenever player enters or leaves the faction
 *
 * @package fpe\event\member
 */
class MembershipChangeEvent extends MemberEvent implements Cancellable
{

    const REASON_CUSTOM = 0x0;
    const REASON_LEAVE = 0x1;
    const REASON_JOIN = 0x2;
    const REASON_CREATE = 0x3;
    const REASON_KICK = 0x4;
    const REASON_DISBAND = 0x5;


    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var Faction */
    protected $faction;

    /** @var int */
    protected $reason;

    public function __construct(IMember $member, Faction $faction, int $reason = self::REASON_CUSTOM)
    {
        parent::__construct($member);
        $this->faction = $faction;
        $this->reason = $reason;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function getReason(): int
    {
        return $this->reason;
    }

    public function setCancelled(bool $force = true): void
    {
        if ($this->reason === self::REASON_CREATE || $this->reason === self::REASON_DISBAND) {
            throw new LogicException(self::class . "(reason={self::$this->reason}) can't be cancelled");
        } else {
            parent::setCancelled($force);
        }
    }

    public function isLeaving(): bool
    {
        return $this->getFaction()->isNone();
    }

}