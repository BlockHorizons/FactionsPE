<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\manager;

use fpe\data\FactionData;
use fpe\entity\Faction;
use fpe\entity\IMember;
use fpe\flag\Flag;
use fpe\permission\Permission;
use fpe\relation\Relation;
use pocketmine\IPlayer;

class Factions
{

    /** @var string[] */
    private static $hashes = [];

    /**
     * @var Faction[]
     */
    private static $factions = [];

    public static function createSpecialFactions()
    {
        if (self::getByName(Faction::NAME_NONE) === null) {
            Factions::attach(new Faction(Faction::NONE, new FactionData([
                "id" => Faction::NONE,
                "name" => Faction::NAME_NONE,
                "flags" => [
                    Flag::PVP => true,
                    Flag::ANIMALS => true,
                    Flag::ENDER_GRIEF => true,
                    Flag::EXPLOSIONS => true,
                    Flag::OFFLINE_EXPLOSIONS => true,
                    Flag::FIRE_SPREAD => true,
                    Flag::FRIENDLY_FIRE => true,
                    Flag::ZOMBIE_GRIEF => true,
                    Flag::PERMANENT => true,
                    Flag::POWER_LOSS => true,
                    Flag::INFINITY_POWER => true
                ],
                "description" => "It's dangerous to go alone", # TODO: Translatable
                "perms" => [
                    Permission::BUILD => Relation::getAll(),
                    Permission::CONTAINER => Relation::getAll(),
                    Permission::BUTTON => Relation::getAll(),
                    Permission::DOOR => Relation::getAll()
                ],
            ])));
        }
        if (self::getByName(Faction::NAME_SAFEZONE) === null) {
            Factions::attach(new Faction(Faction::SAFEZONE, new FactionData([
                "id" => Faction::SAFEZONE,
                "name" => Faction::NAME_SAFEZONE,
                "flags" => [
                    Flag::PEACEFUL => true,
                    Flag::PERMANENT => true,
                    Flag::INFINITY_POWER => true
                ],
                "description" => "Save from PVP and Monsters" # TODO: Translatable
            ])));
        }
        if (self::getByName(Faction::NAME_WARZONE) === null) {
            Factions::attach(new Faction(Faction::WARZONE, new FactionData([
                "id" => Faction::WARZONE,
                "name" => Faction::NAME_WARZONE,
                "flags" => [
                    Flag::PVP => true,
                    Flag::PERMANENT => true,
                    Flag::INFINITY_POWER => true
                ],
                "description" => "Be careful enemies can be nearby" # TODO: Translatable
            ])));
        }
    }

    /**
     * This faction should be manually attached to global store using Factions::attach() method
     * 
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $members = []
     * @param array|Flag[] $flags = [] (flag => bool | Flag[])
     * @param array|Permission[] $perms = [] (permission => array(relations) | Permission[])
     * @param array $data = []
     *
     * @return Faction
     */
    public static function create(string $id, string $name, string $description, array $members = [], array $flags = [], array $perms = [], array $data = []): Faction {
        if(self::getById($id)) {
            throw new \Exception("Can not create faction: id '$id' taken");
        } elseif (self::getByName($name)) {
            throw new \Exception("Can not create faction: name '$name' taken");
        }
        // TODO: Validate description
        return new Faction($id, new FactionData(array_merge([
            "id" => $id,
            "name" => $name,
            "description" => $description,
            "members" => $members,
            "flags" => $flags,
            "perms" => $perms,
            ], $data)));
    }

    /**
     * Creates a member list in correct format. This list can be passed in Faction::__construct method via $data variable
     * or self::create() method via $members variable
     *
     * @param IMember $leader
     * @param IMember[]|string[]|IPlayer[] $officers = []
     * @param IMember[]|string[]|IPlayer[] $members = []
     * @param IMember[]|string[]|IPlayer[] $recruits = []
     *
     * @return array (rank => IMember[])
     */
    public static function createMembersList(IMember $leader, array $officers = [], array $members = [], array $recruits = []): array {
        foreach ($vars = [$officers, $members, $recruits] as $var) {
            foreach ($var as $key => $member) {
                $var[$key] = strtolower(Members::get($member, true)->getName());
            }
        }
        return [
            Relation::LEADER => [strtolower(trim($leader->getName()))],
            Relation::OFFICER => $officers,
            Relation::MEMBER => $members,
            Relation::RECRUIT => $recruits
        ];
    }

    /**
     * @param string $name
     * @return Faction|null
     */
    public static function getByName(string $name): ?Faction
    {
        $name = strtolower(trim($name));
        foreach (self::$factions as $faction) {
            if ($name === strtolower(trim($faction->getName()))) return $faction;
        }
        return null;
    }

    /**
     * @param IPlayer $player
     * @return Faction|null
     */
    public static function getForPlayer(IPlayer $player): ?Faction
    {
        return self::getForMember(Members::get($player));
    }

    public static function getForMember(IMember $member): ?Faction
    {
        foreach (self::$factions as $faction) {
            if ($faction->isMember($member)) return $faction;
        }
        return null;
    }

    /**
     * @param string $id
     * @return Faction|null
     */
    public static function getById(string $id)
    {
        if (isset(self::$factions[$id])) {
            return self::$factions[$id];
        }
        return null;
    }

    public static function attach(Faction $faction)
    {
        if (self::contains($faction)) return;
        self::$factions[$faction->getId()] = $faction;
        self::$hashes[$faction->getId()] = self::hash($faction);
    }

    public static function contains(Faction $faction): bool
    {
        return isset(self::$factions[$faction->getId()]);
    }

    public static function hash(Faction $faction)
    {
        return md5(json_encode($faction->__toArray()));
    }

    /**
     * This can save cpu resources if there's a lot of
     * factions and many of them doesn't really need saving
     */
    public static function saveUnsavedFactions()
    {
        foreach (self::getAll() as $id => $faction) {
            $hash = self::$hashes[$id];
            if (($newHash = self::hash($faction)) !== $hash) {
                $faction->save();
            }
            self::$hashes[$id] = $newHash;
        }
    }

    /**
     * @return Faction[]
     */
    public static function getAll(): array
    {
        return self::$factions;
    }

    public static function detach(Faction $faction)
    {
        if (!self::contains($faction)) return;
        unset(self::$factions[$faction->getId()]);
        unset(self::$hashes[$faction->getId()]);
        unset($faction);
    }

    public static function saveAll()
    {
        foreach (self::getAll() as $faction) {
            $faction->save();
        }
    }

}
