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

class Permission {

	// -------------------------------------------- //
    // CONSTANTS
    // -------------------------------------------- //

    // Permission id
    const BUILD = "build";
    const PAINBUILD = "painbuild";
    const DOOR = "door";
    const BUTTON = "button";
    const LEVER = "lever";
    const CONTAINER = "container";
    const NAME = "name";
    const DESC = "desc";
    const MOTD = "motd";
    const INVITE = "invite";
    const KICK = "kick";
    const TITLE = "title";
    const HOME = "home";
    const SETHOME = "sethome";
    const DEPOSIT = "deposit";
    const WITHDRAW = "withdraw";
    const TERRITORY = "territory";
    const ACCESS = "access";
    const CLAIMNEAR = "claimnear";
    const Relation = "Relation";
    const DISBAND = "disband";
    const FLAGS = "flags";
    const PERMS = "perms";
    const STATUS = "status";
    
    // Priorities
    const PRIORITY_BUILD = 1000;
    const PRIORITY_PAINBUILD = 2000;
    const PRIORITY_DOOR = 3000;
    const PRIORITY_BUTTON = 4000;
    const PRIORITY_LEVER = 5000;
    const PRIORITY_CONTAINER = 6000;
    const PRIORITY_NAME = 7000;
    const PRIORITY_DESC = 8000;
    const PRIORITY_MOTD = 9000;
    const PRIORITY_INVITE = 10000;
    const PRIORITY_KICK = 11000;
    const PRIORITY_TITLE = 12000;
    const PRIORITY_HOME = 13000;
    const PRIORITY_SETHOME = 14000;
    const PRIORITY_DEPOSIT = 15000;
    const PRIORITY_WITHDRAW = 16000;
    const PRIORITY_TERRITORY = 17000;
    const PRIORITY_ACCESS = 18000;
    const PRIORITY_CLAIMNEAR = 19000;
    const PRIORITY_Relation = 20000;
    const PRIORITY_DISBAND = 21000;
    const PRIORITY_FLAGS = 22000;
    const PRIORITY_PERMS = 23000;
    const PRIORITY_STATUS = 24000;
    
    // ------------------------------------ //
    // GLOBAL                               //
    
    // ------------------------------------ //
    /** @var \SplObjectStorage $storage */
    private static $storage;
    
    private $registered = false;
    private $priority = 0;
    private $name = "defaultName";

    // The sort priority. Low values appear first in sorted lists.
    // 1 is high up, 99999 is far down.
    // Standard Faction perms use "thousand values" like 1000, 2000, 3000 etc to allow adding new perms inbetween.
    // So 1000 might sound like a lot but it's actually the priority for the first perm.
    private $desc = "defaultDesc";
    	
    /**
     * Relationations
     */
    private $standard = [];
    
    private $territory = false;

    private $editable = false;
    private $visible = true;

    public function __construct(int $priority, string $name, string $desc, array $standard, bool $territory, bool $editable, bool $visible) {
        $this->priority = $priority;
        $this->name = $name;
        $this->desc = $desc;
        $this->standard = $standard;
        $this->territory = $territory;
        $this->editable = $editable;
        $this->visible = $visible;
    }

    public static function init()
    {
        if (!self::$storage) {
            self::$storage = new \SplObjectStorage();
            self::setupStandardPerms();
        }
    }

    public static function setupStandardPerms()
    {
        self::getPermBuild();
        self::getPermPainbuild();
        self::getPermDoor();
        self::getPermButton();
        self::getPermLever();
        self::getPermContainer();
        self::getPermName();
        self::getPermDesc();
        self::getPermMotd();
        self::getPermInvite();
        self::getPermKick();
        self::getPermTitle();
        self::getPermHome();
        self::getPermStatus();
        self::getPermSethome();
        self::getPermDeposit();
        self::getPermWithdraw();
        self::getPermTerritory();
        self::getPermAccess();
        self::getPermClaimnear();
        self::getPermRelation();
        self::getPermDisband();
        self::getPermFlags();
        self::getPermPerms();
    }
    
    public static function getPermBuild()
    {
        return self::getCreative(self::PRIORITY_BUILD, self::BUILD, self::BUILD, "edit the terrain", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER], true, true, true);
    }
    // What is the standard (aka default) perm value?
    // This value will be set for factions from the beginning.
    // Example: ... set of Relationations ...
    public static function getCreative(int $priority, string $id, string $name, string $desc, array $standard, bool $territory, bool $editable, bool $visible) : Perm
    {
        $ret = self::getById($id);
        if ($ret != null) {
            $ret->setRegistered(true);
            return $ret;
        }
        $ret = new Perm($priority, $name, $desc, $standard, $territory, $editable, $visible);
        self::$storage->attach($ret);
        $ret->setRegistered(true);
        return $ret;
    }
    /**
     * @param string $id
     * @return Perm|NULL
     */
    public static function getById(string $id)
    {
        foreach (self::$storage as $perm) {
            if ($perm->getId() === $id) return $perm;
        }
        return null;
    }
    public static function getPermPainbuild()
    {
        return self::getCreative(self::PRIORITY_PAINBUILD, self::PAINBUILD, self::PAINBUILD, "edit, take damage", [], true, true, true);
    }
    // Is this a territory perm meaning it has to do with territory construction, modification or interaction?
    // True Examples: build, container, door, lever etc.
    // False Examples: name, invite, home, sethome, deposit, withdraw etc.
    public static function getPermDoor() {
        return self::getCreative(self::PRIORITY_DOOR, self::DOOR, self::DOOR, "use doors", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true);
    }

    public static function getPermButton() {
        return self::getCreative(self::PRIORITY_BUTTON, self::BUTTON, self::BUTTON, "use stone buttons", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true);
    }

    public static function getPermLever() {
        return self::getCreative(self::PRIORITY_LEVER, self::LEVER, self::LEVER, "use levers", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true);
    }
    
    public static function getPermContainer() {
        return self::getCreative(self::PRIORITY_CONTAINER, self::CONTAINER, self::CONTAINER, "use containers", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER], true, true, true);
    }

    public static function getPermName() {
        return self::getCreative(self::PRIORITY_NAME, self::NAME, self::NAME, "set name", [Relation::LEADER], false, true, true);
    }

    public static function getPermDesc() {
        return self::getCreative(self::PRIORITY_DESC, self::DESC, self::DESC, "set description", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }
    
    public static function getPermMotd() {
        return self::getCreative(self::PRIORITY_MOTD, self::MOTD, self::MOTD, "set motd", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermInvite() {
        return self::getCreative(self::PRIORITY_INVITE, self::INVITE, self::INVITE, "invite players", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermKick() {
        return self::getCreative(self::PRIORITY_KICK, self::KICK, self::KICK, "kick members", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermTitle() {
        return self::getCreative(self::PRIORITY_TITLE, self::TITLE, self::TITLE, "set titles", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

	public static function getPermHome() {
        return self::getCreative(self::PRIORITY_HOME, self::HOME, self::HOME, "teleport home", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], false, true, true);
    }

    public static function getPermStatus() {
        return self::getCreative(self::PRIORITY_STATUS, self::STATUS, self::STATUS, "show status", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermSethome() {
        return self::getCreative(self::PRIORITY_SETHOME, self::SETHOME, self::SETHOME, "set the home", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermDeposit() {
        return self::getCreative(self::PRIORITY_DEPOSIT, self::DEPOSIT, self::DEPOSIT, "deposit money", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY, Relation::TRUCE, Relation::NEUTRAL, Relation::ENEMY], false, false, false);
    }

    public static function getPermWithdraw() {
        return self::getCreative(self::PRIORITY_WITHDRAW, self::WITHDRAW, self::WITHDRAW, "withdraw money", [Relation::LEADER], false, true, true);
    }
    
    public static function getPermTerritory() {
        return self::getCreative(self::PRIORITY_TERRITORY, self::TERRITORY, self::TERRITORY, "claim or unclaim", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermAccess() {
        return self::getCreative(self::PRIORITY_ACCESS, self::ACCESS, self::ACCESS, "grant territory", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermClaimnear() {
        return self::getCreative(self::PRIORITY_CLAIMNEAR, self::CLAIMNEAR, self::CLAIMNEAR, "claim nearby", [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], false, false, false);
    }

    public static function getPermRelation() {
        return self::getCreative(self::PRIORITY_Relation, self::Relation, self::Relation, "change Relationations", [Relation::LEADER, Relation::OFFICER], false, true, true);
    }

    public static function getPermDisband() {
        return self::getCreative(self::PRIORITY_DISBAND, self::DISBAND, self::DISBAND, "disband the faction", [Relation::LEADER], false, true, true);
    }

    public static function getPermFlags() {
        return self::getCreative(self::PRIORITY_FLAGS, self::FLAGS, self::FLAGS, "manage flags", [Relation::LEADER], false, true, true);
    }

    public static function getPermPerms() {
        return self::getCreative(self::PRIORITY_PERMS, self::PERMS, self::PERMS, "manage permissions", [Relation::LEADER], false, true, true);
    }

    /**
     * @return Permission[]
     */
    public static function getAll() : array {
        $perms = [];
        foreach (self::$storage as $perm) {
            $perms[] = $perm;
        }
        return $perms;
    }

    public static function getStateHeaders() : string
    {
        $ret = "";
        foreach (Relation::getAll() as $Relation) {
            $ret .= Relation::getColor($Relation);
            $ret .= substr($Relation, 0, 3);
            $ret .= " ";
        }
        return $ret;
    }
    public function isRegistered() : BOOL
    {
        return $this->registered;
    }
    public function setRegistered(bool $registered)
    {
        $this->registered = $registered;
    }
    public function getPriority() : INT
    {
        return $this->priority;
    }
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }
    public function getName() : STRING
    {
        return $this->name;
    }
    public function setName(string $name) : Permission
    {
        $this->name = $name;
        return $this;
    }
    public function getDesc() : String
    {
        return $this->desc;
    } // non editable, non visible.
    public function setDesc(string $desc) : Permission
    {
        $this->desc = $desc;
        return $this;
    }
    public function getStandard() : ARRAY
    {
        return $this->standard;
    }
    public function setStandard(array $standard) : Permission
    {
        $this->standard = $standard;
        return $this;
    }
    public function isTerritory() : BOOL
    {
        return $this->territory;
    } // non editable, non visible.
    public function setTerritory(bool $territory) : Permission
    {
        $this->territory = $territory;
        return $this;
    }
    public function has(IFPlayer $player, Faction $faction = NULL) : BOOL
    {
        $faction = !$faction ? $player->getFaction() : $faction;
        $Relation = $faction->getRelationationTo($player);
        $ret =  $player->isOverriding() ? true : $faction->isPermitted($this, $Relation);
        FactionsPE::get()->getLogger()->debug("Permission::".strtoupper($this->getId())."->has({$player->getName()}<$Relation>, {$faction->getName()}) === ".($ret?"true":"false"));
        return $ret;
    }
    public function factionHas(Faction $factionA, Faction $factionB)
    {
        $Relation = $factionA->getRelationationTo($factionB);
        return $factionB->isPermitted($this, $Relation);
    }
    public function getStateInfo(array $Relations, bool $withDesc = false) : STRING
    {
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
    public function isVisible() : BOOL
    {
        return $this->visible;
    }
    public function setVisible(bool $visible) : Permission
    {
        $this->visible = $visible;
        return $this;
    }
    public function isEditable() : BOOL
    {
        return $this->editable;
    }
    public function setEditable(bool $editable)
    {
        $this->editable = $editable;
        return $this;
    }
    // -------------------------------------------- //
    // UTIL: ASCII
    // -------------------------------------------- //
    public function getId() : STRING
    {
        return $this->name;
    }
    public function getDescription() : STRING
    {
        return $this->desc;
    }

}