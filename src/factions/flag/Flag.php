<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\flag;

use localizer\Translatable;

class Flag {

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

    /** @var int */
    private $priority;

    /** @var Translatable */
    protected $desc, $descYes, $descNo;

    /** @var bool */
    protected $standard = true, $editable = false, $visible = true;

    public function __construct(string $id, int $priority, string $name, Translatable $desc, Translatable $descYes, Translatable $descNo, bool $standard, bool $editable, bool $visible) {
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

    public function getPriority() : int {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Flag
     */
    public function setPriority(int $priority) : Flag {
        $this->priority = $priority;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Flag
     */
    public function setName(string $name) : Flag {
        $this->name = $name;
        return $this;
    }

    public function getDesc() : Translatable {
        return $this->desc;
    }

    public function setDesc(Translatable $desc) : Flag {
        $this->desc = $desc;
        return $this;
    }

    public function getDescYes() : Translatable {
        return $this->descYes;
    }

    public function setDescYes(Translatable $descYes) : Flag {
        $this->descYes = $descYes;
        return $this;
    }

    public function getDescNo() : Translatable {
        return $this->descNo;
    }

    public function setDescNo(Translatable $descNo) : Flag {
        $this->descNo = $descNo;
        return $this;
    }

    public function isEditable() : bool {
        return $this->editable;
    }

    public function setEditable(bool $editable) : Flag {
        $this->editable = $editable;
        return $this;
    }

    public function isVisible() : bool {
        return $this->visible;
    }

    public function setVisible(bool $visible) : Flag {
        $this->visible = $visible;
        return $this;
    }

    public function getId() : string {
        return $this->id;
    }

    public function isStandard() : bool {
        return $this->standard;
    }

    public function setStandard(boolean $standard) : Flag {
        $this->standard = $standard;
        return $this;
    }

    /**
     * This is for saving flags
     * NOTE: this array won't contain id of the flag
     */
    public function __toArray() {
        return [
            "name" => $this->name,
            "priority" => $this->priority,
            "desc" => $this->desc->getKey(),
            "descYes" => $this->descYes->getKey(),
            "descNo" => $this->descNo->getKey(),
            "standard" => $this->standard,
            "editable" => $this->editable,
            "visible" => $this->visible
        ];
    }

}