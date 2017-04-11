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

use factions\entity\IMember;
use factions\entity\Member;
use factions\entity\OfflineMember;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\IPlayer;
use pocketmine\Player;

class Members
{

    /**
     * @var IMember[]
     */
    private static $players = [];

    /**
     * Get instanceof IMember by player identifier
     * @param $player string|IPlayer|IMember|CommandSender
     * @return IMember|Member|OfflineMember|null
     */
    public static function get($player, bool $create = true)
    {
        if (!$player) {
            throw new \InvalidArgumentException("argument 1 passed to " . __CLASS__ . "::" . __METHOD__ . " must be IMember, CommandSender or string, " . Text::toString($player, false) . " given");
        }
        if ($player instanceof ConsoleCommandSender) {
            return self::get("console");
        }
        if ($player instanceof IMember) {
            if ($player instanceof OfflineMember) {
                $player = $player->getName();
            } else {
                $player = $player->getPlayer();
            }
        }
        if ($player instanceof IPlayer) {
            // Handling player object
            if (($ret = self::getByName($player->getName())) !== null) {
                // if stored instance is offline - detach from storage
                if (($ret instanceof Member) === false) {
                    self::detach($ret);
                }
                return $ret;
            }
            return self::create($player);
        } else {
            // Handling name. It can be part of online player name, or exact offline player name
            if (($po = FactionsPE::get()->getServer()->getPlayer($player))) {
                return Members::get($po);
            }
            if (($ret = self::getByName($player)) !== null) {
                return $ret;
            }
            if ($create)
                return self::createOffline($player);
        }
    }

    /**
     * This function won't create new IMember instance it will just check
     * is there offline/online player stored in self::$players
     * with the same name
     *
     * @return IMember|null
     * @param $name
     */
    public static function getByName($name)
    {
        foreach (self::$players as $player) {
            if (strtolower($player->getName()) === strtolower($name)) return $player;
        }
        return null;
    }

    /**
     * Remove a member from storage
     * @param IMember $player
     */
    public static function detach(IMember $player)
    {
        if (self::contains($player)) {
            unset(self::$players[$player->getName()]);
            if (!$player->isDefault()) {
                $player->save();
            }
        }
    }

    public static function contains(IMember $player): bool
    {
        return isset(self::$players[$player->getName()]);
    }

    /**
     * Create a Member instance for Player if he doesn't have one already
     * @param Player $player
     */
    public static function create(Player $player)
    {
        if (($ret = self::getByName($player->getName())) !== NULL) return $ret;
        return new Member($player);
    }

    /**
     * Returns a OfflinePlayer instance for player with $name
     * @param string $name
     */
    public static function createOffline(string $name): OfflineMember
    {
        if (($ret = self::getByName($name)) !== NULL) return $ret;
        return new OfflineMember($name);
    }

    public static function getFactionless(): array
    {
        $r = [];
        foreach (self::$players as $player) {
            if (!$player->isOnline()) continue;
            if ($player->hasFaction()) continue;
            $r[] = $player;
        }
        return $r;
    }

    public static function attach(IMember $player)
    {
        if (!self::contains($player)) self::$players[$player->getName()] = $player;
    }

    /**
     * @return IMember[]
     */
    public static function getAllOnline(): ARRAY
    {
        $ret = [];
        foreach (self::$players as $Member) {
            if ($Member->isOnline()) $ret[] = $Member;
        }
        return $ret;
    }

    /**
     * Makes sure that every member data is saved
     */
    public static function saveAll()
    {
        foreach (self::getAll() as $player) {
            $player->save();
        }
    }

    /**
     * @return IMember[]
     */
    public static function getAll()
    {
        return self::$players;
    }

    public static function debug()
    {
        var_dump([
            "objects" => count(self::getAll()),
            "players" => array_map(function ($item) {
                return $item->getName();
            }, self::getAll())
        ]);
    }

}
