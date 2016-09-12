<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/27/16
 * Time: 4:04 PM
 */

namespace factions\objs;


use factions\data\DataProvider;
use factions\entity\Faction;
use factions\entity\Flag;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\utils\UUID;

class Factions
{

    private static $instance;
    /** @var \SplObjectStorage $storage */
    protected $storage;

    public function __construct() {
        self::$instance = $this;
        $this->storage = new \SplObjectStorage();
        
        DataProvider::get()->loadSavedFactions();
        self::createSpecialFactions();
    }

    public static function createSpecialFactions()
    {
        if(Factions::getByName(FactionsPE::NAME_NONE_DEFAULT) === NULL) {
            new Faction(FactionsPE::NAME_NONE_DEFAULT, FactionsPE::FACTION_ID_NONE, [
                "flags" => [
                    Flag::PVP,
                    Flag::ANIMALS,
                    Flag::ENDER_GRIEF,
                    Flag::EXPLOSIONS,
                    Flag::FIRE_SPREAD,
                    Flag::FRIENDLY_FIRE,
                    Flag::ZOMBIE_GRIEF,
                    Flag::PERMANENT,
                    Flag::POWER_LOSS,
                    Flag::INFINITY_POWER
                ],
                "description" => "It's dangerous to go alone",
                "perms" => [
                    Perm::BUILD => [
                        Perm::getAll(),
                    ],
                    Perm::CONTAINER => [
                        Perm::getAll(),
                    ],
                    Perm::BUTTON => [
                        Perm::getAll(),
                    ],
                    Perm::DOOR => [
                        Perm::getAll(),
                    ]
                ],
            ]);
        }
        if(Factions::getByName(FactionsPE::NAME_SAFEZONE_DEFAULT) === NULL) {
            new Faction(FactionsPE::NAME_SAFEZONE_DEFAULT, FactionsPE::FACTION_ID_SAFEZONE, [
                "flags" => [
                    Flag::PEACEFUL,
                    Flag::PERMANENT,
                    Flag::INFINITY_POWER
                ],
                "description" => "Save from PVP and Monsters"
            ]);
        }
        if(Factions::getByName(FactionsPE::NAME_WARZONE_DEFAULT) === NULL) {
            new Faction(FactionsPE::NAME_WARZONE_DEFAULT, FactionsPE::FACTION_ID_WARZONE, [
                "flags" => [
                    Flag::PVP,
                    Flag::PERMANENT,
                    Flag::INFINITY_POWER
                ],
                "description" => "Be careful enemies can be nearby"
            ]);
        }
    }

    public static function isRegistered(Faction $faction)
    {
        return self::get()->storage->contains($faction);
    }

    public static function get() : Factions
    {
        return self::$instance;
    }

    public static function attach(Faction $faction)
    {
        self::get()->storage->attach($faction);
    }

    public static function detach(Faction $faction)
    {
        self::get()->storage->detach($faction);
    }

    /**
     * @param string $name
     * @return Faction|NULL
     */
    public static function getByName(string $name)
    {
        foreach (self::get()->storage as $faction) {
            if (strtolower($faction->getName()) === strtolower($name)) return $faction;
        }
        return NULL;
    }

    public static function create($name, FPlayer $player){
        $faction = new Faction($name, UUID::generate($name), []);
        $player->setFactionId($faction->getId());
        $player->setFaction($faction);
        $player->setRole(Rel::LEADER);
        return $faction->verifyMember($player);
    }

    /**
     * @param string $id
     * @return Faction|null
     */
    public static function getById(string $id)
    {
        foreach (self::get()->storage as $faction) {
            if ($faction->getId() === $id) return $faction;
        }
        return NULL;
    }

    /**
     * @return Faction[]
     */
    public static function getAll()
    {
        $r = [];
        foreach(self::get()->storage as $i => $f)
            $r[$i] = $f;
        return $r;
    }

    public static function close()
    {
        echo __CLASS__." closed\n";
        self::get()->save();
        self::$instance = null;
    }

    // ------------------------------------------- //
    // Setup default factions
    // ------------------------------------------- //

    public function save()
    {
        foreach ($this->storage as $faction) {
            DataProvider::get()->saveFactionData($faction, false);
        }
    }
    
}