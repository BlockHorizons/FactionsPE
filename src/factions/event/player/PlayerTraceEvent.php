<?php
namespace factions\event\player;

use factions\interfaces\IFPlayer;
use pocketmine\event\Event;

/**
 * This event is called when player moves from plot to plot
 */
class PlayerTraceEvent extends Event
{

    public static $handlerList = NULL;

    /** @var string */
    protected $from;
    protected $to;

    public function __construct(IFPlayer $player, string $fromFactionId, string $toFactionId)
    {
        $this->player = $player;
        $this->from = $fromFactionId;
        $this->to = $toFactionId;
    }

    public function getPlayer() : IFPlayer
    {
        return $this->player;
    }

    public function getFromFactionId() : STRING
    {
        return $this->from;
    }

    public function getToFactionId() : STRING
    {
        return $this->to;
    }
}