<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\flag;

use fpe\localizer\Translatable;
use fpe\utils\Text;
use pocketmine\utils\TextFormat;

class Flag
{

    const OPEN = "open";
    const PERMANENT = "permanent";
    const PEACEFUL = "peaceful";
    const INFINITY_POWER = "inf_power";
    const POWER_LOSS = "power_loss";
    const PVP = "pvp";
    const FRIENDLY_FIRE = "friendly_fire";
    const MONSTERS = "monsters";
    const ANIMALS = "animals";
    const EXPLOSIONS = "explosions";
    const OFFLINE_EXPLOSIONS = "offline_explosions";
    const FIRE_SPREAD = "fire_spread";
    const ENDER_GRIEF = "ender_grief";
    const ZOMBIE_GRIEF = "zombie_grief";

    const PRIORITY_OPEN = 1000;
    const PRIORITY_MONSTERS = 2000;
    const PRIORITY_ANIMALS = 3000;
    const PRIORITY_POWER_LOSS = 4000;
    const PRIORITY_FRIENDLY_FIRE = 5000;
    const PRIORITY_OFFLINE_EXPLOSIONS = 6000;
    const PRIORITY_EXPLOSIONS = 7000;
    const PRIORITY_FIRE_SPREAD = 8000;
    const PRIORITY_ENDER_GRIEF = 9000;
    const PRIORITY_ZOMBIE_GRIEF = 10000;
    const PRIORITY_PERMANENT = 11000;
    const PRIORITY_PEACEFUL = 12000;
    const PRIORITY_INFINITY_POWER = 13000;
    const PRIORITY_PVP = 14000;

    /** @var string */
    protected $id, $name;
    /** @var Translatable */
    protected $desc, $descYes, $descNo;
    /** @var bool */
    protected $standard = true, $editable = false, $visible = true;
    /** @var int */
    private $priority;

    public function __construct(string $id, int $priority, string $name, Translatable $desc, Translatable $descYes, Translatable $descNo, bool $standard, bool $editable, bool $visible)
    {
        $this->id = $id;
        $this->priority = $priority;
        $this->name = $name;
        $this->desc = $desc;
        $this->descYes = $descYes;
        $this->descNo = $descNo;
        $this->visible = $visible;
        $this->editable = $editable;
        $this->standard = $standard;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Flag
     */
    public function setPriority(int $priority): Flag
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param string $name
     * @return Flag
     */
    public function setName(string $name): Flag
    {
        $this->name = $name;
        return $this;
    }

    public function setDescYes(Translatable $descYes): Flag
    {
        $this->descYes = $descYes;
        return $this;
    }

    public function setDescNo(Translatable $descNo): Flag
    {
        $this->descNo = $descNo;
        return $this;
    }

    public function setEditable(bool $editable): Flag
    {
        $this->editable = $editable;
        return $this;
    }

    public function setVisible(bool $visible): Flag
    {
        $this->visible = $visible;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStateDesc(bool $value, bool $withValue, bool $monospaceValue, bool $withName, bool $withDesc, bool $specificDesc)
    {
        $ret = [];

        // Fill
        if ($withValue) {
            $ret[] = $this->getStateValue($value, $monospaceValue);
        }

        if ($withName) {
            $ret[] = $this->getStateName();
        }

        if ($withDesc) {
            $ret[] = $this->getStateDescription($value, $specificDesc);
        }

        // Return
        return implode(" ", $ret);
    }

    public function getStateValue(bool $value, bool $monoSpace = true)
    {
        $yes = "<g>YES";
        $no = $monoSpace ? "<b>NOO" : "<b>NO";

        return Text::parse($value ? $yes : $no);
    }

    public function getStateName(): string
    {
        return $this->getStateColor() . $this->getName();
    }

    public function getStateColor(): string
    {
        if (!$this->isVisible()) {
            return TextFormat::GRAY;
        }

        if ($this->isEditable()) {
            return TextFormat::LIGHT_PURPLE;
        }

        return TextFormat::AQUA;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStateDescription(?bool $value = null): string
    {
        if ($value !== null) {
            return $value ? $this->getDescYes() : $this->getDescNo();
        }
        return $this->getDesc(); // NOTICE: In Java factions, it returns this string in yellow
    }

    public function getDescYes(): Translatable
    {
        return $this->descYes;
    }

    public function getDescNo(): Translatable
    {
        return $this->descNo;
    }

    public function getDesc(): Translatable
    {
        return $this->desc;
    }

    public function setDesc(Translatable $desc): Flag
    {
        $this->desc = $desc;
        return $this;
    }

    public function isInteresting(bool $value): bool
    {
        if (!$this->isVisible()) {
            return false;
        }

        if (!$this->isEditable()) {
            return true;
        }

        return $this->isStandard() !== $value;
    }

    public function isStandard(): bool
    {
        return $this->standard;
    }

    public function setStandard(boolean $standard): Flag
    {
        $this->standard = $standard;
        return $this;
    }

    /**
     * This is for saving flags
     * NOTE: this array won't contain id of the flag
     */
    public function __toArray()
    {
        return [
            "name" => $this->name,
            "priority" => $this->priority,
            "desc" => $this->desc->getKey(),
            "descYes" => $this->descYes->getKey(),
            "descNo" => $this->descNo->getKey(),
            "standard" => $this->standard,
            "editable" => $this->editable,
            "visible" => $this->visible,
        ];
    }

}