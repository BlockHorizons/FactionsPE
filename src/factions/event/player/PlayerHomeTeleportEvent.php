<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/29/16
 * Time: 10:11 AM
 */

namespace factions\event\player;

use pocketmine\event\Cancellable;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\event\Event;

class PlayerHomeTeleportEvent extends Event implements Cancellable
{

    public static $handlerList = null;

    protected $player;
    protected $destination;

    public function __construct(Player $player, Position $destination)
    {
        $this->player = $player;
        $this->destination = $destination;
    }

    /**
     * @return Position
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param Position $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function getPlayer() : Player {
        return $this->player;
    }

}