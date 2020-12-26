<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\event\faction;

use fpe\entity\Faction;
use fpe\entity\IMember;
use fpe\permission\Permission;
use pocketmine\event\Cancellable;

class FactionPermissionChangeEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    public $member, $perm, $rel, $value, $oldValue;

    public function __construct(IMember $member, Faction $faction, Permission $perm, string $rel, bool $value)
    {
        parent::__construct($faction);

        $this->member = $member;
        $this->perm = $perm;
        $this->rel = $rel;
        $this->value = $value;
        $this->oldValue = $faction->isPermitted($perm, $rel);
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return $this->perm;
    }

    /**
     * @return string
     */
    public function getRelation()
    {
        return $this->rel;
    }

    /**
     * @return IMember
     */
    public function getMember()
    {
        return $this->member;
    }

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function getNewValue()
    {
        return $this->value;
    }

    public function setValue(bool $value)
    {
        $this->value = $value;
    }

}