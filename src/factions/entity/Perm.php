<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/27/16
 * Time: 5:24 AM
 */

namespace factions\entity;


use factions\interfaces\IFPlayer;
use factions\interfaces\RelationParticipator;
use factions\objs\Rel;
use factions\utils\Text;

class Perm
{
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
    const REL = "rel";
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
    const PRIORITY_REL = 20000;
    const PRIORITY_DISBAND = 21000;
    const PRIORITY_FLAGS = 22000;
    const PRIORITY_PERMS = 23000;
    const PRIORITY_STATUS = 24000;

    // ------------------------------------ //
    // GLOBAL                               //
    // ------------------------------------ //

    /** @var \SplObjectStorage $storage */
    private static $storage;

    // -------------------------------------------- //
    // TRANSIENT FIELDS (Registered)
    // -------------------------------------------- //

    private $registered = false;
    private $priority = 0;
    private $name = "defaultName";

    // -------------------------------------------- //
    // FIELDS
    // -------------------------------------------- //

    // The sort priority. Low values appear first in sorted lists.
    // 1 is high up, 99999 is far down.
    // Standard Faction perms use "thousand values" like 1000, 2000, 3000 etc to allow adding new perms inbetween.
    // So 1000 might sound like a lot but it's actually the priority for the first perm.
    private $desc = "defaultDesc";
    private $standard = [];
    private $territory = false;

    // The name of the perm. According to standard it should be fully lowercase just like the perm id.
    // In fact the name and the id of all standard perms are the same.
    // I just added the name in case anyone feel like renaming their perms for some reason.
    // Example: "build"
    private $editable = false;
    private $visible = true;

    public function __construct(int $priority, string $name, string $desc, array $standard, bool $territory, bool $editable, bool $visible)
    {
        $this->priority = $priority;
        $this->name = $name;
        $this->desc = $desc;
        $this->standard = $standard;
        $this->territory = $territory;
        $this->editable = $editable;
        $this->visible = $visible;
    }

    // The perm function described as an "order".
    // The desc should match the format:
    // "You are not allowed to X."
    // "You are not allowed to edit the terrain."
    // Example: "edit the terrain"

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
        self::getPermRel();
        self::getPermDisband();
        self::getPermFlags();
        self::getPermPerms();
    }

    public static function getPermBuild()
    {
        return self::getCreative(self::PRIORITY_BUILD, self::BUILD, self::BUILD, "edit the terrain", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER], true, true, true);
    }

    // What is the standard (aka default) perm value?
    // This value will be set for factions from the beginning.
    // Example: ... set of relations ...

    public static function getCreative(int $priority, string $id, string $name, string $desc, array $standard, bool $territory, bool $editable, bool $visible) : Perm
    {
        $ret = self::getPermById($id);
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
    public static function getPermById(string $id)
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

    public static function getPermDoor()
    {
        return self::getCreative(self::PRIORITY_DOOR, self::DOOR, self::DOOR, "use doors", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY], true, true, true);
    }

    public static function getPermButton()
    {
        return self::getCreative(self::PRIORITY_BUTTON, self::BUTTON, self::BUTTON, "use stone buttons", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY], true, true, true);
    }

    public static function getPermLever()
    {
        return self::getCreative(self::PRIORITY_LEVER, self::LEVER, self::LEVER, "use levers", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY], true, true, true);
    }

    // Is this perm editable by players?
    // With this we mean standard non administrator players.
    // All perms can be changed using /f override.
    // Example: true (all perms are editable by default)

    public static function getPermContainer()
    {
        return self::getCreative(self::PRIORITY_CONTAINER, self::CONTAINER, self::CONTAINER, "use containers", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER], true, true, true);
    }

    public static function getPermName()
    {
        return self::getCreative(self::PRIORITY_NAME, self::NAME, self::NAME, "set name", [Rel::LEADER], false, true, true);
    }

    public static function getPermDesc()
    {
        return self::getCreative(self::PRIORITY_DESC, self::DESC, self::DESC, "set description", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    // Is this perm visible to players?
    // With this we mean standard non administrator players.
    // All perms can be seen using /f override.
    // Some perms can be rendered meaningless by settings in Factions or external plugins.
    // Say we set "editable" to false.
    // In such case we might want to hide the perm by setting "visible" false.
    // If it can't be changed, why bother showing it?
    // Example: true (yeah we need to see this permission)

    public static function getPermMotd()
    {
        return self::getCreative(self::PRIORITY_MOTD, self::MOTD, self::MOTD, "set motd", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermInvite()
    {
        return self::getCreative(self::PRIORITY_INVITE, self::INVITE, self::INVITE, "invite players", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermKick()
    {
        return self::getCreative(self::PRIORITY_KICK, self::KICK, self::KICK, "kick members", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermTitle()
    {
        return self::getCreative(self::PRIORITY_TITLE, self::TITLE, self::TITLE, "set titles", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermHome()
    {
        return self::getCreative(self::PRIORITY_HOME, self::HOME, self::HOME, "teleport home", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY], false, true, true);
    }

    public static function getPermStatus()
    {
        return self::getCreative(self::PRIORITY_STATUS, self::STATUS, self::STATUS, "show status", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermSethome()
    {
        return self::getCreative(self::PRIORITY_SETHOME, self::SETHOME, self::SETHOME, "set the home", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermDeposit()
    {
        return self::getCreative(self::PRIORITY_DEPOSIT, self::DEPOSIT, self::DEPOSIT, "deposit money", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY, Rel::TRUCE, Rel::NEUTRAL, Rel::ENEMY], false, false, false);
    }

    public static function getPermWithdraw()
    {
        return self::getCreative(self::PRIORITY_WITHDRAW, self::WITHDRAW, self::WITHDRAW, "withdraw money", [Rel::LEADER], false, true, true);
    }

    // ------------------------------------ //
    //  BUILDING DEFAULT PERMISSIONS
    // ------------------------------------ //

    public static function getPermTerritory()
    {
        return self::getCreative(self::PRIORITY_TERRITORY, self::TERRITORY, self::TERRITORY, "claim or unclaim", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermAccess()
    {
        return self::getCreative(self::PRIORITY_ACCESS, self::ACCESS, self::ACCESS, "grant territory", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermClaimnear()
    {
        return self::getCreative(self::PRIORITY_CLAIMNEAR, self::CLAIMNEAR, self::CLAIMNEAR, "claim nearby", [Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT, Rel::ALLY], false, false, false);
    }

    public static function getPermRel()
    {
        return self::getCreative(self::PRIORITY_REL, self::REL, self::REL, "change relations", [Rel::LEADER, Rel::OFFICER], false, true, true);
    }

    public static function getPermDisband()
    {
        return self::getCreative(self::PRIORITY_DISBAND, self::DISBAND, self::DISBAND, "disband the faction", [Rel::LEADER], false, true, true);
    }

    public static function getPermFlags()
    {
        return self::getCreative(self::PRIORITY_FLAGS, self::FLAGS, self::FLAGS, "manage flags", [Rel::LEADER], false, true, true);
    }

    public static function getPermPerms()
    {
        return self::getCreative(self::PRIORITY_PERMS, self::PERMS, self::PERMS, "manage permissions", [Rel::LEADER], false, true, true);
    }

    /**
     * @return Perm[]
     */
    public static function getAll() : ARRAY
    {
        $perms = [];
        foreach (self::$storage as $perm) {
            $perms[] = $perm;
        }
        return $perms;
    }

    public static function getStateHeaders() : STRING
    {
        $ret = "";
        foreach (Rel::getAll() as $rel) {
            $ret .= Rel::getColor($rel);
            $ret .= substr($rel, 0, 3);
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

    public function setName(string $name) : Perm
    {
        $this->name = $name;
        return $this;
    }

    public function getDesc() : String
    {
        return $this->desc;
    } // non editable, non visible.

    public function setDesc(string $desc) : Perm
    {
        $this->desc = $desc;
        return $this;
    }

    public function getStandard() : ARRAY
    {
        return $this->standard;
    }

    public function setStandard(array $standard) : Perm
    {
        $this->standard = $standard;
        return $this;
    }

    public function isTerritory() : BOOL
    {
        return $this->territory;
    } // non editable, non visible.

    public function setTerritory(bool $territory) : Perm
    {
        $this->territory = $territory;
        return $this;
    }

    public function has(IFPlayer $player, Faction $faction = NULL) : BOOL
    {
        $faction = !$faction ? $player->getFaction() : $faction;
        $rel = $faction->getRelationTo($player);
        $ret =  $player->isOverriding() ? true : $faction->isPermitted($this, $rel);
        \evalcore\EvalCore::debug("Perm::".strtoupper($this->getId())."->has({$player->getName()}<$rel>, {$faction->getName()}) === ".($ret?"true":"false"));
        return $ret;
    }

    public function factionHas(Faction $factionA, Faction $factionB)
    {
        $rel = $factionA->getRelationTo($factionB);
        return $factionB->isPermitted($this, $rel);
    }

    public function getStateInfo(array $rels, bool $withDesc = false) : STRING
    {
        $ret = "";

        foreach (Rel::getAll() as $rel) {
            if (in_array($rel, $rels, true)) {
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

    public function setVisible(bool $visible) : Perm
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