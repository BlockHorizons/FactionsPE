<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/1/16
 * Time: 4:24 PM
 */

namespace factions\event\player;


use factions\base\EventBase;
use factions\entity\Faction;
use factions\interfaces\IFPlayer;
use pocketmine\event\Cancellable;

class PlayerMembershipChangeEvent extends EventBase implements Cancellable
{

    public static $handlerList = NULL;

    const REASON_JOIN = 0x001;
    const REASON_CREATE = 0x002;
    const REASON_KICK = 0x003;
    const REASON_LEAVE = 0x004;
    const REASON_DISBAND = 0x005;
    const REASON_RANK = 0x006;
    const REASON_CUSTOM = 0x000f;

    /**
     * @var IFPlayer $player
     */
    protected $player;
    /**
     * To what faction?
     * Want to get old faction? Use $player->getFaction();
     * @var
     */
    protected $newFaction;
    /**
     * Whats the reason for membership change?
     * @var int
     */
    protected $reason;

    public function __construct(IFPlayer $player, Faction $newFaction, $reason = self::REASON_CUSTOM){
        $this->player = $player;
        $this->newFaction = $newFaction;
        $this->reason = $reason;
    }

    public function getPlayer() {
        return $this->player;
    }

    public function getReason() {
        return $this->reason;
    }

    public function getNewFaction() {
        return $this->newFaction;
    }

}