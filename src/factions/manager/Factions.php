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

use pocketmine\IPlayer;

use factions\entity\IFaction;
use factions\entity\Faction;
use factions\flag\Flag;
use factions\permission\Permission;

class Factions {

  /**
   * @var IFaction[]
   */
  private static $factions = [];

  public static function createSpecialFactions() {
    if(self::getByName(Faction::NAME_NONE) === null) {
      new Faction(Faction::NONE, [
          "name" => Faction::NAME_NONE,
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
          "description" => "It's dangerous to go alone", # TODO: Translatable
          "perms" => [
              Permission::BUILD => [
                  Permission::getAll(),
              ],
              Permission::CONTAINER => [
                  Permission::getAll(),
              ],
              Permission::BUTTON => [
                  Permission::getAll(),
              ],
              Permission::DOOR => [
                  Permission::getAll(),
              ]
          ],
      ]);
    }
    if(self::getByName(Faction::NAME_SAFEZONE) === null) {
        new Faction(Faction::SAFEZONE, [
            "name" => Faction::NAME_SAFEZONE,
            "flags" => [
                Flag::PEACEFUL,
                Flag::PERMANENT,
                Flag::INFINITY_POWER
            ],
            "description" => "Save from PVP and Monsters" # TODO: Translatable
        ]);
    }
    if(self::getByName(Faction::NAME_WARZONE) === null) {
        new Faction(Faction::WARZONE, [
            "name" => Faction::NAME_WARZONE,
            "flags" => [
                Flag::PVP,
                Flag::PERMANENT,
                Flag::INFINITY_POWER
            ],
            "description" => "Be careful enemies can be nearby" # TODO: Translatable
        ]);
    }
  }

  /**
   * @return IFaction|null
   */
  public static function getForPlayer(IPlayer $player) {
    foreach(self::$factions as $faction) {
      if($player->isMember($player)) return $faction;
    }
    return null;
  }

  /**
   * @return IFaction|null
   */
   public static function getById(string $id) {
     if(isset(self::$factions[$id])) {
       return self::$factions[$id];
     }
     return null;
   }

   /**
    * @return IFaction|null
    */
    public static function getByName(string $name) {
      $name = strtolower(trim($name));
      foreach (self::$factions as $faction) {
        if($name === strtolower(trim($faction->getName()))) return $faction;
      }
      return null;
    }

    public static function contains(IFaction $faction) : bool {
      return isset(self::$factions[$faction->getId()]);
    }

    public static function attach(IFaction $faction) {
      if(self::contains($faction)) return;
      self::$factions[$faction->getId()] = $faction;
    }

    public static function detach(IFaction $faction) {
      if(!self::contains($faction)) return;
      unset(self::$factions[$faction->getId()]);
    }

    public static function getAll() : array {
      return self::$factions;
    }

    public static function saveAll() {
      foreach (self::getAll() as $faction) {
        $faction->save();
      }
    }

}
