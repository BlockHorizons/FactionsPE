<?php
namespace factions\event\player;

use factions\base\EventBase;
use factions\interfaces\IFPlayer;

/**
 * @author Primus
 *
 * This event is called when player moves from plot to plot
 */
class PlayerTraceEvent extends EventBase
{

    public static $handlerList = NULL;

    protected $from;
    protected $to;

    public function __construct(IFPlayer $player, $fromFactionId, $toFactionId)
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