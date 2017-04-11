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

use dominate\parameter\Parameter;
use economizer\Economizer;
use economizer\Transistor;
use factions\command\FactionCommand;
use factions\data\provider\DataProvider;
use factions\data\provider\JSONDataProvider;
use factions\data\provider\MySQLDataProvider;
use factions\data\provider\SQLite3DataProvider;
use factions\data\provider\YAMLDataProvider;
use factions\engine\ChatEngine;
use factions\engine\CombatEngine;
use factions\engine\ExploitEngine;
use factions\engine\MainEngine;
use factions\entity\Faction;
use factions\entity\FConsole;
use factions\manager\Factions;
use factions\manager\Flags;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\manager\Plots;
use factions\task\HUD;
use factions\task\PowerUpdateTask;
use factions\utils\Gameplay;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\plugin\PluginBase;
use sll\LibLoader;

define("IN_DEV", file_exists(dirname(__FILE__) . "/.dev"));

class FactionsPE extends PluginBase
{

    private static $engines = [
        MainEngine::class,
        ChatEngine::class,
        CombatEngine::class,
        ExploitEngine::class
    ];

    /** @var FactionsPE */
    private static $instance;

    /** @var DataProvider */
    protected $dataProvider;

    /** @var Economizer|Transistor */
    protected $economy;

    /**
     * Get current instance
     * @return FactionsPE
     */
    public static function get(): FactionsPE
    {
        return self::$instance;
    }

    /**
     * Prepare libraries and load config for further use
     */
    public function onLoad()
    {
        self::$instance = $this;
        // This is disabled as I moved these libraries from plugin/lib folder
        // I'll return this when I update sub-modules
        // LibLoader::loadLib($this->getFile(), "Localizer");
        // LibLoader::loadLib($this->getFile(), "Economizer");
        // LibLoader::loadLib($this->getFile(), "Dominate");

        @mkdir($this->getDataFolder());
        if (!is_dir($tar = $this->getDataFolder() . "languages")) {
            Localizer::transferLanguages($this->getFile() . "resources/languages", $tar);
        }
        Localizer::loadLanguages($tar);
        Localizer::setParser(function (string $text) {
            return Text::parse($text);
        });

        # Save & Load config
        if(!file_exists($cf = $this->getDataFolder() . "config.yml")) {
            file_put_contents($cf, $c = stream_get_contents($f = $this->getResource("config.yml")));
            fclose($f);
        }
        $this->saveDefaultConfig();

        if (Localizer::checkLanguageExistence($lan = $this->getConfig()->get('language'))) {
            Localizer::$globalLocale = strtolower(trim($lan));
        } else {
            $this->getLogger()->warning(Localizer::trans('plugin.invalid-locale', ["locale" => $lan]));
        }
    }


    public function onEnable()
    {
        $this->getLogger()->info(Localizer::trans("plugin.enabling"));

        # Load DataProvider
        if (!$this->loadDataProvider()) goto stop;

        # Load Integrations
        if (!$this->loadIntegrations()) goto stop;

        # Load Gameplay settings
        Gameplay::setData($this->getConfig()->get('gameplay', []));

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

        // Delete inactive ones
        $week = 3600 * 24 * 7;
        foreach (Factions::getAll() as $faction) {
            if ($faction->isSpecial()) continue;
            $lp = $faction->getLastOnline();
            if (time() - $lp > $week) {
                $this->getLogger()->info($faction->getName() . " was disbanded. Last online: " . Text::ago($lp));
                $faction->disband(Faction::DISBAND_REASON_PURGE, true);
            }
        }

        Factions::createSpecialFactions();
        $this->getLogger()->info(Localizer::trans("factions-loaded", [count(Factions::getAll())]));

        # Load Plots
        $this->getDataProvider()->loadPlots();

        # attach Console object
        Members::attach(new FConsole());

        # Register engines
        $this->runEngines();

        # Schedule update task
        if (Gameplay::get("power.update-enabled", true)) {
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new PowerUpdateTask($this), Gameplay::get("power.update-every", 10) * 20 * 60);
            $this->getLogger()->info(Localizer::trans("plugin.power-update-enabled"));
        }

        # Schedule HUD task
        if (Gameplay::get("hud.enabled", true)) {
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new HUD($this), 15);
            $this->getLogger()->info(Localizer::trans("plugin.hud-enabled"));
        }

        # Run tests
        if (IN_DEV) {
            $this->runTests();
        }

        $this->getLogger()->info(Localizer::trans("plugin.enabled"));
        return;
        stop:
        $this->getServer()->getPluginManager()->disablePlugin($this);
    }

    /**
     * @internal
     */
    private function loadDataProvider(): bool
    {
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
            $this->getLogger()->critical(Localizer::trans('plugin.dataprovider-error', [$e->getMessage()]));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        $this->getLogger()->info(Localizer::trans('plugin.dataprovider-set', [$this->getDataProvider()->getName()]));
        return true;
    }

    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    public function setDataProvider(DataProvider $provider)
    {
        $this->dataProvider = $provider;
    }

    /**
     * @internal
     */
    private function loadIntegrations(): bool
    {
        $stop = false;
        if ($this->economyEnabled()) {
            $n = $this->getConfig()->get('economy-plugin', Economizer::DEFAULT_API);
            $plugin = $this->getServer()->getPluginManager()->getPlugin($n);
            if (!$plugin) {
                $this->getLogger()->info(Localizer::trans("economy-plugin-not-found", ["name" => $n]));
                $stop = true;
                goto end;
            }

            $t = Economizer::getTransistorFor($plugin);
            if (!$t) {
                $this->getLogger()->info(Localizer::trans("economy-plugin-not-supported", ["name" => $n]));
                $stop = true;
                goto end;
            }

            $this->economy = new Economizer($this, $t);
            if ($this->economy->ready()) {
                $this->getLogger()->info(Localizer::trans("economy-plugin-selected", ["name" => $this->economy->getName()]));
            } else {
                $this->getLogger()->info(Localizer::trans("economy-not-ready", ["name" => $this->economy->getName()]));
            }
        }
        // If chat-formatter is set to false, then we assume that user is using PureChat
        if($this->getConfig()->get("chat-formatter")) {
            $pc = $this->getServer()->getPluginManager()->getPlugin("PureChat");
            if($pc !== null && $pc->isEnabled()) {
                self::$engines[ChatEngine::class]->setPureChat($pc);
                $this->getLogger()->info(Localizer::trans("chat-formatter-set", [
                    "plugin" => "PureChat"
                ]));
            }
        }
        end:
        return !$stop;
    }

    /**
     * Returns true if economy support is turned on
     */
    public function economyEnabled(): bool
    {
        return (bool)$this->getConfig()->get('economy-support', true);
    }

    /**
     * @internal
     */

    private function runEngines()
    {
        foreach (self::$engines as $engine) {
            $this->getServer()->getPluginManager()->registerEvents($e = new $engine($this), $this);
            self::$engines[$engine] = $e;
        }
    }

    /**
     * @internal
     */
    private function runTests()
    {
        $tests = glob($this->getFile() . "test/*_test.php");

        foreach ($tests as $test) {
            $this->getLogger()->info("Running " . $test . "");
            $code = file_get_contents($test);
            $code = substr($code, strpos($code, "<?php") + 5);
            try {
                eval($code);
            } catch (\Exception $e) {
                $this->getLogger()->error("Error while executing a test: " . $e->getMessage());
            }
        }

    }

    public function onDisable()
    {

        $this->getLogger()->info(Localizer::trans('plugin.disabling'));
        if (!empty($d = Gameplay::getData())) {
            $this->getConfig()->set('gameplay', $d);
        }

        Members::saveAll();
        Factions::saveAll();
        Plots::saveAll();
        Flags::saveAll();
        Permissions::saveAll();

        Flags::flush();

        if ($this->getDataProvider() instanceof DataProvider) {
            $this->getDataProvider()->close();
        }

        //$this->getConfig()->save();
        $this->getLogger()->info(Localizer::trans('plugin.disabled'));
    }

    /**
     * @return Economizer|null
     */
    public function getEconomy()
    {
        return $this->economy;
    }

}