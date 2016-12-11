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
namespace factions\data;

use factions\FactionsPE;
use factions\entity\IMember;
use factions\entity\Faction;

abstract class DataProvider {

  /** @var FactionsPE */
  protected $main;

  /**
   * DataProvider constructor
   * @param FactionsPE $main
   * @throws \Exception
   */
  public function __construct(FactionsPE $main) {
    $this->main = $main;
    $this->prepare();
  }

  /**
   * @param $file
   * @param bool $create
   * @param string $default
   * @return string
   */
  public static function readFile($file, $create = false, $default = "") : string
  {
      $file = str_replace("__DATAFOLDER__", FactionsPE::getFolder(), $file);
      if (!file_exists($file)) {
          if ($create === true)
              file_put_contents($file, $default);
          else
              return $default;
      }
      return file_get_contents($file);
  }
  /**
   * @param $file
   * @param $data
   */
  public static function writeFile($file, $data){
      $file = str_replace("__DATAFOLDER__", FactionsPE::getFolder(), $file);
      $f = fopen($file, "w");
      if($f){
          fwrite($f, $data);
      }
      fclose($f);
  }

  protected function getMain() : FactionsPE {
    return $this->main;
  }

  // ---------------------------------------------------------------------------
  // ABSTRACT FUNCTIONS
  // ---------------------------------------------------------------------------

  protected abstract function prepare();

  public abstract function saveMember(IMember $member);
  public abstract function saveFaction(IFaction $faction);

  public abstract function loadMember(string $name);
  public abstract function loadFaction(string $id);

  /**
   * @param string
   */
  public abstract function deleteMember(string $identifier);
  /**
   * @param string
   */
  public abstract function deleteFaction(string $identifier);

  public abstract function loadFactions();

  public abstract function close();

}
