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
namespace factions\utils;

final class Gameplay {

  private function __construct() {}

  /**
   * @var mixed[]
   */
  private static $data = [];

  // ---------------------------------------------------------------------------
  // FUNCTIONS
  // ---------------------------------------------------------------------------

  public static function setData(array $data) {
    self::$data = $data;
  }

  public static function getData() : array {
    return self::$data;
  }

  public static function set(string $key, $value) {
    self::$data[$key] = $value;
  }

  public static function get(string $key, $default = null) {
    $data = self::$data;
        if(strpos($key, ".") !== false) {
            $keys = explode(".", $key);
            $i = 0;
            while(isset($data[$keys[$i]])) {
                $data = $data[$keys[$i]];
                if(!is_array($data)) return $data;
                $i++;
                if(!isset($keys[$i])) return $data;
            }
        }
        if(isset($data[$key])) return $data[$key];
        return $default;
  }

}
