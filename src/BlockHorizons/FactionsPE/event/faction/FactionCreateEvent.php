<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\event\faction;

use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\entity\IMember;
use pocketmine\event\Cancellable;

class FactionCreateEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var string */
    protected $name, $factionId;

    /** @var IMember */
    protected $creator;

    public function __construct(IMember $creator, string $factionId, string $name)
    {
        $this->name = $name;
        $this->factionId = $factionId;
        $this->creator = $creator;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        if (!empty($errors = Faction::validateName($name))) {
            $e = "Name '$name' is invalid. Found " . count($errors) . " while validating:" . PHP_EOL;
            foreach ($errors as $i => $error) {
                $e .= ($i + 1) . ": " . $error . ($i !== count($errors) - 1 ? PHP_EOL : "");
            }
            throw new \InvalidArgumentException($e);
        }
        $this->name = $name;
    }

    public function getFactionId()
    {
        return $this->factionId;
    }


}