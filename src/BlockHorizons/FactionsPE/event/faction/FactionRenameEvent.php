<?php

namespace BlockHorizons\FactionsPE\event\faction;

use BlockHorizons\FactionsPE\entity\Faction;
use pocketmine\event\Cancellable;

class FactionRenameEvent extends FactionEvent implements Cancellable
{

    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;

    /** @var string */
    protected $name, $oldName;

    public function __construct(Faction $faction, string $newName)
    {
        parent::__construct($faction);
        $this->name = $newName;
        $this->oldName = $faction->getName();
    }

    public function getNewName(): string
    {
        return $this->name;
    }

    public function getOldName(): string
    {
        return $this->oldName;
    }

    public function setNewName(string $name)
    {
        // Should be validated?
        $this->name = $name;
    }

}