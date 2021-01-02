<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event\faction;

use BlockHorizons\FactionsPE\data\FactionData;
use pocketmine\event\Cancellable;

class FactionLoadEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var string */
    protected string $factionId;

    /** @var FactionData */
    protected FactionData $data;

    public function __construct(string $factionId, FactionData $data)
    {
        parent::__construct(null);

        $this->factionId = $factionId;
        $this->data = $data;
    }

    public function getFactionId(): string
    {
        return $this->factionId;
    }

    public function getData(): FactionData
    {
        return $this->data;
    }

    public function setData(FactionData $data): void
    {
        $this->data = $data;
    }

}