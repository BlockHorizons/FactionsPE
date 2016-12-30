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

namespace factions;

use pocketmine\plugin\PluginBase;

use localizer\Localizer;
use economizer\Economizer;

use sll\LibLoader;

use factions\utils\Gameplay;
use factions\utils\Text;
use factions\command\FactionCommand;
use factions\data\provider\DataProvider;
use factions\data\provider\YAMLDataProvider;
use factions\data\provider\JSONDataProvider;
use factions\manager\Factions;
use factions\manager\Members;
use factions\manager\Plots;
use factions\manager\Flags;
use factions\manager\Permissions;
use factions\entity\FConsole;
use factions\permission\Permission;
use factions\engine\MainEngine;
use factions\engine\ChatEngine;

define("IN_DEV", file_exists(dirname(__FILE__)."/.dev"));

class FactionsPE extends PluginBase {

  private static $engines = [
    MainEngine::class,
    ChatEngine::class,
  ];

  /** @var FactionsPE */
  private static $instance;

  /** @var DataProvider */
  protected $dataProvider;

  /**
   * Get current instance
   * @return FactionsPE
   */
  public static function get() : FactionsPE {
    return self::$instance;
  }

  /**
   * Prepare libraries and load config for further use
   */
  public function onLoad() {
    self::$instance = $this;
    LibLoader::loadLib($this->getFile(), "Localizer");
    LibLoader::loadLib($this->getFile(), "Economizer");
    LibLoader::loadLib($this->getFile(), "Dominate");

    @mkdir($this->getDataFolder());
    if(!is_dir($tar = $this->getDataFolder()."languages")) {
      Localizer::transferLanguages($this->getFile()."resources/languages", $tar);
    }
    Localizer::loadLanguages($tar);
    Localizer::setParser(function(string $text){
      return Text::parse($text);
    });
    //$this->saveDefaultConfig();
    if(Localizer::checkLanguageExistence($lan = $this->getConfig()->get('language'))) {
      Localizer::$globalLocale = strtolower(trim($lan));
    } else {
      $this->getLogger()->warning(Localizer::trans('plugin.invalid-locale', ["locale" => $lan]));
    }
  }

  public function onEnable() {
    $this->getLogger()->info(Localizer::trans("plugin.enabling"));

    # Load DataProvider
    if(!$this->loadDataProvider()) goto stop;
    # Load Integrations
    if(!$this->loadIntegrations()) goto stop;
    # Load Gameplay settings
    Gameplay::setData($this->getConfig()->get('gameplay'));
    # Load flags
    $this->getDataProvider()->loadFlags();
    Flags::init();
    # Load Permissions
    $this->getDataProvider()->loadPermissions();
    Permissions::init();
    # Register commands
    $this->getServer()->getCommandMap()->register("faction", new FactionCommand($this));
    # Load factions
    $this->getDataProvider()->loadFactions();
    Factions::createSpecialFactions();
    $this->getLogger()->info(Localizer::trans("factions-loaded", [count(Factions::getAll())]));
    # Load Plots
    $this->getDataProvider()->loadPlots();
    # attach Console object
    Members::attach(new FConsole());
    # Register engines
    $this->runEngines()/**
   * @internal
   */;

    # Run tests
    if(IN_DEV) {
      $this->runTests();
    }

    $this->getLogger()->info(Localizer::trans("plugin.enabled"));

    return;
    stop:
    $this->getServer()->getPluginManager()->disablePlugin($this);
  }

  public function onDisable() {
    $this->getLogger()->info(Localizer::trans('plugin.disabling'));
    if(!empty($d = Gameplay::getData())) {
      $this->getConfig()->set('gameplay', $d);
    }

    Members::saveAll();
    Factions::saveAll();
    Plots::saveAll();
    Flags::saveAll();
    Permissions::saveAll();

    if($this->getDataProvider() instanceof DataProvider) {
      $this->getDataProvider()->close();
    }

    $this->getConfig()->save();
    $this->getLogger()->info(Localizer::trans('plugin.disabled'));
  }

  // ---------------------------------------------------------------------------
  // LOADER FUNCTIONS
  // ---------------------------------------------------------------------------

  /**
   * @internal
   */
  private function loadDataProvider() : bool {
    try {
      switch (strtolower(trim($this->getConfig()->get('data-provider')["type"]))) {
        default:
        case 'yaml':
        case 'yml':
          $this->setDataProvider(new YAMLDataProvider($this));
          break;
        case 'json':
          $this->setDataProvider(new JSONDataProvider($this));
          break;
        case 'sql':
        case 'sqlite':
        case 'sqlite3':
          $this->setDataProvider(new SQLite3DataProvider($this));
          break;
        case 'mysql':
          $this->setDataProvider(new MySQLDataProvider($this));
          break;
      }
    } catch (\Exception $e) {
      $this->getLogger()->error(Localizer::trans('plugin.dataprovider-error', [$e->getMessage()]));
      $this->getServer()->getPluginManager()->disablePlugin($this);
      return false;
    }
    $this->getLogger()->info(Localizer::trans('plugin.dataprovider-set', [$this->getDataProvider()->getName()]));
    return true;
  }

  public function getDataProvider() {
    return $this->dataProvider;
  }

  public function setDataProvider(DataProvider $provider) {
    $this->dataProvider = $provider;
  }

  /**
   * @internal
   */
  private function loadIntegrations() : bool {
    $stop = false;
    if($this->economyEnabled()) {

      $n = $this->getConfig()->get('economy-plugin', Economizer::DEFAULT_API);
      $plugin = $this->getServer()->getPluginManager()->getPlugin($n);
      if(!$plugin) {
        $this->getLogger()->info(Localizer::trans("economy-plugin-not-found", ["name" => $n]));
        $stop = true;
        goto end;
      }
      $t = Economizer::getTransistorFor($plugin);
      if(!$t) {
        $this->getLogger()->info(Localizer::trans("economy-plugin-not-supported", ["name" => $n]));
        $stop = true;
        goto end;
      }
      $this->economy = new Economizer($this, $t);

      if($this->economy->ready()) {
        $this->getLogger()->info(Localizer::trans("economy-plugin-selected", ["name" => $this->economy->getName()]));
      } else {
        $this->getLogger()->info(Localizer::trans("economy-not-ready", ["name" => $this->economy->getName()]));
      }
    }
    end:
    return !$stop;
  }

  /**
   * Returns true if economy support is turned on
   */
  public function economyEnabled() : bool {
    return (bool) $this->getConfig()->get('economy-support', true);
  }

  /**
   * @return Economizer|null
   */
  public function getEconomy() {
    return $this->economy;
  }

  /**
   * @internal
   */
  private function runTests() {
    $tests = glob($this->getFile()."test/*_test.php");
    foreach($tests as $test) {
      $this->getLogger()->info("Running ".$test."");
      $code = file_get_contents($test);
      $code = substr($code, strpos($code, "<?php") + 5);
      try {
        eval($code);
      } catch(\Exception $e) {
        $this->getLogger()->error("Error while executing a test: ".$e->getMessage());
      }
    }
  }

  /**
   * @internal
   */
  private function runEngines() {
    foreach (self::$engines as $engine) {
      $this->getServer()->getPluginManager()->registerEvents(new $engine($this), $this);
    }
  }

}
