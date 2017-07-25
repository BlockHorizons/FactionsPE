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

namespace factions\manager;

use factions\entity\Faction;
use factions\entity\IMember;
use factions\flag\Flag;
use factions\permission\Permission;
use factions\relation\Relation;
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
            Factions::attach(new Faction(Faction::NONE, [
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
            ]));
        }
        if (self::getByName(Faction::NAME_SAFEZONE) === null) {
            Factions::attach(new Faction(Faction::SAFEZONE, [
                "name" => Faction::NAME_SAFEZONE,
                "flags" => [
                    Flag::PEACEFUL => true,
                    Flag::PERMANENT => true,
                    Flag::INFINITY_POWER => true
                ],
                "description" => "Save from PVP and Monsters" # TODO: Translatable
            ]));
        }
        if (self::getByName(Faction::NAME_WARZONE) === null) {
            Factions::attach(new Faction(Faction::WARZONE, [
                "name" => Faction::NAME_WARZONE,
                "flags" => [
                    Flag::PVP => true,
                    Flag::PERMANENT => true,
                    Flag::INFINITY_POWER => true
                ],
                "description" => "Be careful enemies can be nearby" # TODO: Translatable
            ]));
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
        return new Faction($id, array_merge([
            "name" => $name,
            "description" => $description,
            "members" => $members,
            "flags" => $flags,
            "perms" => $perms,
            ], $data));
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
                $var[$key] = Members::get($member, true);
            }
        }
        return [
            Relation::LEADER => [$leader],
            Relation::OFFICER => $officers,
            Relation::MEMBER => $members,
            Relation::RECRUIT => $recruits
        ];
    }

    /**
     * @param string $name
     * @return Faction|null
     */
    public static function getByName(string $name)
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
    public static function getForPlayer(IPlayer $player)
    {
        return self::getForMember(Members::get($player));
    }

    public static function getForMember(IMember $member)
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
