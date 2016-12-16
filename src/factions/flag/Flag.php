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

class Flag {
	// ------------------- //
    // CONTANTS            //
    // ------------------- //

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
    const PRIORITY_FIRES_PREAD = 8000;
    const PRIORITY_ENDER_GRIEF = 9000;
    const PRIORITY_ZOMBIE_GRIEF = 10000;
    const PRIORITY_PERMANENT = 11000;
    const PRIORITY_PEACEFUL = 12000;
    const PRIORITY_INFINITY_POWER = 13000;
    const PRIORITY_PVP = 14000;

    /** @var Flag[] $flags */
    private static $flags = [];

    // -------------------------------------------- //
    // TRANSIENT FIELDS (Registered)
    // -------------------------------------------- //
    protected $id;
    private $registered = false;
    private $priority = 0;
    private $name = "defaultName";

    // -------------------------------------------- //
    // FIELDS
    // -------------------------------------------- //


    // The sort priority. Low values appear first in sorted lists.
    // 1 is high up, 99999 is far down.
    // Standard Faction flags use "thousand values" like 1000, 2000, 3000 etc to allow adding new flags inbetween.
    // So 1000 might sound like a lot but it's actually the priority for the first flag.
    private $desc = "defaultDesc";
    private $descYes = "defaultDescYes";
    private $descNo = "defaultDescNo";

    // The name of the flag. According to standard it should be fully lowercase just like the flag id.
    // In fact the name and the id of all standard flags are the same.
    // I just added the name in case anyone feel like renaming their flags for some reason.
    // Example: "monsters"
    private $standard = true;
    private $editable = false;
    private $visible = true;

    // The flag function described as a question.
    // Example: "Can monsters spawn in this territory?"

    public function __construct(string $id, int $priority, string $name, string $desc, string $descYes, string $descNo, bool $standard, bool $editable, bool $visible)
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

    public static function init()
    {
        self::setupStandardFlags();
    }

    public static function setupStandardFlags()
    {
        self::getFlagOpen();
        self::getFlagMonsters();
        self::getFlagAnimals();
        self::getFlagPowerloss();
        self::getFlagPvp();
        self::getFlagFriendlyire();
        self::getFlagExplosions();
        self::getFlagOfflineexplosions();
        self::getFlagFirespread();
        self::getFlagEndergrief();
        self::getFlagZombiegrief();
        self::getFlagPermanent();
        self::getFlagPeaceful();
        self::getFlagInfpower();
    }

    // The flag function described when true.
    // Example: "Monsters can spawn in this territory."

    public static function getFlagOpen() : Flag
    {
        return self::getCreative(self::PRIORITY_OPEN, self::OPEN, self::OPEN, "Can the faction be joined without an invite?", "Anyone can join. No invite required.", "An invite is required to join.", true, true, true);
    }

    public static function getCreative(int $priority, string $id, string $name, string $desc, string $descYes, string $descNo, bool $standard, bool $editable, bool $visible)
    {
        $ret = self::getFlagById($id);
        if ($ret != null) {
            $ret->setRegistered(true);
            return $ret;
        }

        $ret = new Flag($id, $priority, $name, $desc, $descYes, $descNo, $standard, $editable, $visible);
        self::$flags[$id] = $ret;
        $ret->setRegistered(true);
        //$ret->sync();

        return $ret;
    }

    public static function getFlagMonsters() : Flag
    {
        return self::getCreative(self::PRIORITY_MONSTERS, self::MONSTERS, self::MONSTERS, "Can monsters spawn in this territory?", "Monsters can spawn in this territory.", "Monsters can NOT spawn in this territory.", false, true, true);
    }

    // The flag function described when false.
    // Example: "Monsters can NOT spawn in this territory."

    public static function getFlagAnimals() : Flag
    {
        return self::getCreative(self::PRIORITY_ANIMALS, self::ANIMALS, self::ANIMALS, "Can animals spawn in this territory?", "Animals can spawn in this territory.", "Animals can NOT spawn in this territory.", true, true, true);
    }

    public static function getFlagPowerloss() : Flag
    {
        return self::getCreative(self::PRIORITY_POWER_LOSS, self::POWER_LOSS, self::POWER_LOSS, "Is power lost on death in this territory?", "Power is lost on death in this territory.", "Power is NOT lost on death in this territory.", true, false, true);
    }

    public static function getFlagPvp() : Flag
    {
        return self::getCreative(self::PRIORITY_PVP, self::PVP, self::PVP, "Can you PVP in territory?", "You can PVP in this territory.", "You can NOT PVP in this territory.", true, false, true);
    }

    // What is the standard (aka default) flag value?
    // This value will be set for factions from the beginning.
    // Example: false (per default monsters do not spawn in faction territory)

    public static function getFlagFriendlyire() : Flag
    {
        return self::getCreative(self::PRIORITY_FRIENDLY_FIRE, self::FRIENDLY_FIRE, self::FRIENDLY_FIRE, "Can allies hurt each other in this territory?", "Friendly fire is on here.", "Friendly fire is off here.", false, false, true);
    }

    public static function getFlagExplosions() : Flag
    {
        return self::getCreative(self::PRIORITY_EXPLOSIONS, self::EXPLOSIONS, self::EXPLOSIONS, "Can explosions occur in this territory?", "Explosions can occur in this territory.", "Explosions can NOT occur in this territory.", true, false, true);
    }

    public static function getFlagOfflineexplosions() : Flag
    {
        return self::getCreative(self::PRIORITY_OFFLINE_EXPLOSIONS, self::OFFLINE_EXPLOSIONS, self::OFFLINE_EXPLOSIONS, "Can explosions occur if faction is offline?", "Explosions if faction is offline.", "No explosions if faction is offline.", false, false, true);
    }

    // Is this flag editable by players?
    // With this we mean standard non administrator players.
    // All flags can be changed using /f override.
    // Example: true (if players want to turn mob spawning on I guess they should be able to)

    public static function getFlagFirespread() : Flag
    {
        return self::getCreative(self::PRIORITY_FIRES_PREAD, self::FIRE_SPREAD, self::FIRE_SPREAD, "Can fire spread in territory?", "Fire can spread in this territory.", "Fire can NOT spread in this territory.", true, false, true);
    }

    public static function getFlagEndergrief() : Flag
    {
        return self::getCreative(self::PRIORITY_ENDER_GRIEF, self::ENDER_GRIEF, self::ENDER_GRIEF, "Can endermen grief in this territory?", "Endermen can grief in this territory.", "Endermen can NOT grief in this territory.", false, false, true);
    }

    public static function getFlagZombiegrief() : Flag
    {
        return self::getCreative(self::PRIORITY_ZOMBIE_GRIEF, self::ZOMBIE_GRIEF, self::ZOMBIE_GRIEF, "Can zombies break doors in this territory?", "Zombies can break doors in this territory.", "Zombies can NOT break doors in this territory.", false, false, true);
    }

    // Is this flag visible to players?
    // With this we mean standard non administrator players.
    // All flags can be seen using /f override.
    // Some flags can be rendered meaningless by settings in Factions or external plugins.
    // Say we set "editable" to false and "standard" to true for the "open" flag to force all factions being open.
    // In such case we might want to hide the open flag by setting "visible" false.
    // If it can't be changed, why bother showing it?
    // Example: true (yeah we need to see this flag)

    public static function getFlagPermanent() : Flag
    {
        return self::getCreative(self::PRIORITY_PERMANENT, self::PERMANENT, self::PERMANENT, "Is the faction immune to deletion?", "The faction can NOT be deleted.", "The faction can be deleted.", false, false, true);
    }

    public static function getFlagPeaceful() : Flag
    {
        return self::getCreative(self::PRIORITY_PEACEFUL, self::PEACEFUL, self::PEACEFUL, "Is the faction in truce with everyone?", "The faction is in truce with everyone.", "The faction relations work as usual.", false, false, true);
    }

    public static function getFlagInfpower() : Flag
    {
        return self::getCreative(self::PRIORITY_INFINITY_POWER, self::INFINITY_POWER, self::INFINITY_POWER, "Does the faction have infinite power?", "The faction has infinite power.", "The faction power works as usual.", false, false, true);
    }

    public function isRegistered() : BOOL
    {
        return $this->registered;
    }

    public function setRegistered(bool $registered)
    {
        $this->registered = $registered;
    }

    public function getPriority() : int
    {
        return $this->priority;
    }

    public function setPriority(int $priority) : Flag
    {
        $this->priority = $priority;
        return $this;
    }

    public function getName() : STRING
    {
        return $this->name;
    }

    public function setName(string $name) : Flag
    {
        $this->name = $name;
        return $this;
    }

    public function getDesc() : STRING
    {
        return $this->desc;
    }

    public function setDesc(string $desc) : Flag
    {
        $this->desc = $desc;
        return $this;
    }

    public function getDescYes()
    {
        return $this->descYes;
    }

    public function setDescYes(string $descYes) : Flag
    {
        $this->descYes = $descYes;
        return $this;
    }

    public function getDescNo()
    {
        return $this->descNo;
    }

    public function setDescNo(string $descNo) : Flag
    {
        $this->descNo = $descNo;
        return $this;
    }

    public function isEditable() : BOOL
    {
        return $this->editable;
    }

    public function setEditable(bool $editable) : Flag
    {
        $this->editable = $editable;
        return $this;
    }

    public function isVisible() : BOOL
    {
        return $this->visible;
    }

    public function setVisible(bool $visible) : Flag
    {
        $this->visible = $visible;
        return $this;
    }

    public function isFlagStandard(string $flag)
    {
        $flag = self::getFlagById($flag);
        if ($flag === NULL) return false;
        return $flag->isStandard();
    }

    public static function getFlagById(string $id)
    {
        foreach (self::getAll() as $flag) {
            if ($flag->getId() === $id) return $flag;
        }
        return NULL;
    }

    public static function getAll() : ARRAY
    {
        return self::$flags;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isStandard()
    {
        return $this->standard;
    }

    public function setStandard(boolean $standard)
    {
        $this->standard = $standard;
        return $this;
    }

    public function save() : BOOL
    {
        // TODO: Implement save() method.
        return true;
    }

}