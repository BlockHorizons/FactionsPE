<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/12/16
 * Time: 11:00 PM
 */

namespace factions\event\faction;

use factions\entity\Faction;
use factions\entity\Perm;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\Player;

class FactionPermChangeEvent extends Event implements Cancellable
{
    
    public static $handlerList = null;

    public $player, $faction, $perm, $rel, $value, $oldValue;

    public function __construct(Player $player, Faction $faction, Perm $perm, string $rel, bool $value) {
        $this->player = $player;
        $this->faction = $faction;
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
     * @return Perm
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
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    public function getOldValue() {
        return $this->oldValue;
    }

    public function getNewValue() {
        return $this->value;
    }

    public function setValue(bool $value) {
        $this->value = $value;
    }

}