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

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\IPlayer;

use factions\entity\IMember;
use factions\entity\Member;
use factions\entity\OfflineMember;

class Members {

  /**
   * @var IMember[]
   */
  private static $players = [];

  /**
   * You should only pass player names when manipulating with offline players
   *
   * @param $player
   * @return IMember|Member|OfflineMember|null
   */
  public static function get($player, bool $create = true) : IMember
  {
    if($player instanceof ConsoleCommandSender) {
      return self::get("Console");
    }
    if($player instanceof IMember) {
      if($player instanceof OfflineMember) {
        $player = $player->getName();
      } else {
        $player = $player->getPlayer();
      }
    }
    if($player instanceof IPlayer) {
      // Handling player object
      if (($ret = self::getByName($player->getName())) !== null) {
        // if stored instance is offline - detach from storage
        if(($ret instanceof Member) === false) {
          self::detach($ret);
        }
      }
      return self::create($player);
    } else {
      // Handling name aka offline player
      if (($ret = self::getByName($player)) !== null) {
        if($ret->isOnline()) {
          if(!$ret->isNone()) self::detach($ret); // player was stored in offline instance altough he's online
          else return $ret;
        }
      }
      if($create)
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
  public static function getByName($name) {
    foreach(self::$players as $player){
      if(strtolower($player->getName()) === strtolower($name)) return $player;
    }
    return null;
  }

  public static function attach(IMember $player)
  {
    if (!self::contains($player)) self::$players[$player->getName()] = $player;
  }

  public static function contains(IMember $player) : bool
  {
    return isset(self::$players[$player->getName()]);
  }

  /**
   * Remove a member from storage
   * @param IMember $player
   */
  public static function detach(IMember $player)
  {
    if (self::contains($player)) {
      unset(self::$players[$player->getName()]);
      if ($player instanceof Member and $player->hasFaction()) {
        self::get($player->getName());
      }
    }
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
  public static function createOffline(string $name) : OfflineMember
  {
    if (($ret = self::getByName($name)) !== NULL) return $ret;
    return new OfflineMember($name);
  }
  /**
   * @return IMember[]
   */
  public static function getAllOnline() : ARRAY
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
  public static function saveAll() {
    foreach(self::getAll() as $player) {
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

}
