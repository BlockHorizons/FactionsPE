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

namespace factions\permission;

use factions\utils\Text;
use factions\relation\Relation;
use factions\FactionsPE;
use factions\entity\IMember;
use factions\manager\Permissions;

use localizer\Translatable;

class Permission {

	const BUILD     = "build";
    const PAINBUILD = "painbuild";
    const DOOR      = "door";
    const BUTTON    = "button";
    const LEVER     = "lever";
    const CONTAINER = "container";
    const NAME      = "name";
    const DESC      = "desc";
    const MOTD      = "motd";
    const INVITE    = "invite";
    const KICK      = "kick";
    const TITLE     = "title";
    const HOME      = "home";
    const SETHOME   = "sethome";
    const DEPOSIT   = "deposit";
    const WITHDRAW  = "withdraw";
    const TERRITORY = "territory";
    const ACCESS    = "access";
    const CLAIMNEAR = "claimnear";
    const RELATION  = "Relation";
    const DISBAND   = "disband";
    const FLAGS     = "flags";
    const PERMS     = "perms";
    const STATUS    = "status";
    
    const PRIORITY_BUILD        = 1000;
    const PRIORITY_PAINBUILD    = 2000;
    const PRIORITY_DOOR         = 3000;
    const PRIORITY_BUTTON       = 4000;
    const PRIORITY_LEVER        = 5000;
    const PRIORITY_CONTAINER    = 6000;
    const PRIORITY_NAME         = 7000;
    const PRIORITY_DESC         = 8000;
    const PRIORITY_MOTD         = 9000;
    const PRIORITY_INVITE       = 10000;
    const PRIORITY_KICK         = 11000;
    const PRIORITY_TITLE        = 12000;
    const PRIORITY_HOME         = 13000;
    const PRIORITY_SETHOME      = 14000;
    const PRIORITY_DEPOSIT      = 15000;
    const PRIORITY_WITHDRAW     = 16000;
    const PRIORITY_TERRITORY    = 17000;
    const PRIORITY_ACCESS       = 18000;
    const PRIORITY_CLAIMNEAR    = 19000;
    const PRIORITY_RELATION     = 20000;
    const PRIORITY_DISBAND      = 21000;
    const PRIORITY_FLAGS        = 22000;
    const PRIORITY_PERMS        = 23000;
    const PRIORITY_STATUS       = 24000;

    protected $priority = 0;
    protected $name;

    /** @var Translatable */
    protected $desc;
    	
    /**
     * Relationations
     * @var string[]
     */
    private $standard = [];
    
    /**
     * True if permission is related to territory
     */
    private $territory = false;

    /**
     * Can Permission be edited
     */
    private $editable = false;

    /**
     * Is permission visible
     */
    private $visible = true;

    public function __construct(int $priority, string $name, Translatable $desc, array $standard, bool $territory, bool $editable, bool $visible) {
        $this->priority = $priority;
        $this->name = $name;
        $this->desc = $desc;
        $this->standard = $standard;
        $this->territory = $territory;
        $this->editable = $editable;
        $this->visible = $visible;
    }

    public static function getStateHeaders() : string {
        $ret = "";
        foreach (Relation::getAll() as $relation) {
            $ret .= Relation::getColor($relation);
            $ret .= substr($relation, 0, 3);
            $ret .= " ";
        }
        return $ret;
    }

    public function isRegistered() : bool {
        return Permissions::contains($this);
    }

    public function getPriority() : int {
        return $this->priority;
    }

    public function setPriority(int $priority) : Permission {
        $this->priority = $priority;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : Permission {
        $this->name = $name;
        return $this;
    }

    public function getDescription() : Translatable {
        return $this->desc;
    }

    public function setDescription(string $desc) : Permission {
        $this->desc = $desc;
        return $this;
    }

    public function getStandard() : array {
        return $this->standard;
    }

    /**
     * @param array $standard relation ids
     */
    public function setStandard(array $standard) : Permission {
        $this->standard = $standard;
        return $this;
    }

    public function isTerritory() : bool {
        return $this->territory;
    }

    public function setTerritory(bool $territory) : Permission {
        $this->territory = $territory;
        return $this;
    }

    public function has(IMember $player, Faction $faction = null) : bool {
        $faction = !$faction ? $player->getFaction() : $faction;
        $Relation = $faction->getRelationationTo($player);
        $ret =  $player->isOverriding() ? true : $faction->isPermitted($this, $Relation);
        return $ret;
    }

    public function factionHas(Faction $factionA, Faction $factionB) {
        $Relation = $factionA->getRelationationTo($factionB);
        return $factionB->isPermitted($this, $Relation);
    }

    public function getStateInfo(array $Relations, bool $withDesc = false) : string {
        $ret = "";
        foreach (Relation::getAll() as $Relation) {
            if (in_array($Relation, $Relations, true)) {
                $ret .= "<g>YES";
            } else {
                $ret .= "<b>NOO";
            }
            $ret .= " ";
        }
        $color = "<aqua>";
        if (!$this->isVisible()) {
            $color = "<silver>";
        } elseif ($this->isEditable()) {
            $color = "<pink>";
        }
        $ret .= $color;
        $ret .= $this->getId();
        if ($withDesc) $ret .= " <i>" . $this->getDescription();
        $ret = Text::parse($ret);
        return $ret;
    }

    public function isVisible() : bool {
        return $this->visible;
    }

    public function setVisible(bool $visible) : Permission {
        $this->visible = $visible;
        return $this;
    }

    public function isEditable() : bool {
        return $this->editable;
    }

    public function setEditable(bool $editable) {
        $this->editable = $editable;
        return $this;
    }

    public function getId() : string {
        return $this->name;
    }

    public function __toArray() {
        return [
            "priority" => $this->priority,
            "desc" => $this->desc->getKey(),
            "standard" => $this->standard,
            "territory" => $this->territory,
            "editable" => $this->editable,
            "visible" => $this->visible
        ];
    }

}