<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe;

use fpe\economizer\Economizer;
use fpe\economizer\Transistor;
use fpe\dominate\Command;
use fpe\command\FactionCommand;
use fpe\data\provider\DataProvider;
use fpe\data\provider\JSONDataProvider;
use fpe\data\provider\MySQLDataProvider;
use fpe\data\provider\SQLite3DataProvider;
use fpe\data\provider\YAMLDataProvider;
use fpe\engine\BoardEngine;
use fpe\engine\ChatEngine;
use fpe\engine\CombatEngine;
use fpe\engine\Engine;
use fpe\engine\ExploitEngine;
use fpe\engine\MainEngine;
use fpe\engine\SeeChunkEngine;
use fpe\entity\Faction;
use fpe\entity\FConsole;
use fpe\form\FactionForm;
use fpe\manager\Factions;
use fpe\manager\Flags;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\manager\Plots;
use fpe\task\HUD;
use fpe\task\PowerUpdateTask;
use fpe\utils\Gameplay;
use fpe\utils\Text;
use fpe\localizer\Localizer;
use jasonwynn10\ScoreboardAPI\ScoreboardAPI;
use pocketmine\plugin\PluginBase;

define("IN_DEV", file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".dev"));

class FactionsPE extends PluginBase {

    /** @var string[] */
	private static $registeredEngines = [
		MainEngine::class,
		ChatEngine::class,
		CombatEngine::class,
		ExploitEngine::class,
		SeeChunkEngine::class,
	];

	/** @var Engine[] */
	private static array $engines = [];

	/** @var FactionsPE */
	private static FactionsPE $instance;

	/** @var DataProvider */
	private DataProvider $dataProvider;

	/** @var Economizer|Transistor|null */
	private $economy;

	/** @var FormAPI|null */
	private ?FormAPI $formAPI;

	/** @var FactionForm */
	private FactionForm $form;

	/**
	 * Get current instance
	 * @return FactionsPE
	 */
	public static function get(): FactionsPE {
		return self::$instance;
	}

	public function onLoad() {
		self::$instance = $this;
		@mkdir($this->getDataFolder());
		if (!is_dir($tar = $this->getDataFolder() . "languages")) {
			Localizer::transferLanguages($this->getFile() . "resources/languages", $tar);
		}
		Localizer::loadLanguages($tar);
		Localizer::setParser(function (string $text) {
			return Text::parse($text);
		});

		# Save & Load config
		if (!file_exists($cf = $this->getDataFolder() . "config.yml")) {
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

	public function onEnable() {
		$this->getLogger()->debug(Localizer::trans("plugin.enabling"));

		# Load DataProvider
		if (!$this->loadDataProvider()) {
			goto stop;
		}

		# Load Gameplay settings
		Gameplay::setData($this->getConfig()->get('gameplay', []));

		# Load flags
		Flags::flush();
		$this->getDataProvider()->loadFlags();
		Flags::init();

		# Load Permissions
		Permissions::flush();
		$this->getDataProvider()->loadPermissions();
		Permissions::init();

		# Register commands
		$this->getServer()->getCommandMap()->register("FactionsPE", $fc = new FactionCommand($this));

		# Load form handler
		$this->form = new FactionForm($this, $fc);

		# Load factions
		$this->getDataProvider()->loadFactions();

		// Delete inactive ones
		// This should be configurable
		$this->purgeInactiveFactions();

		Factions::createSpecialFactions();
		$this->getLogger()->debug(Localizer::trans("factions-loaded", [count(Factions::getAll())]));

		# Load Plots
        Plots::$CHUNK_SIZE = (int) min(max($this->getConfig()->get('claim-size', 4), 1), 6);
		$this->getDataProvider()->loadPlots();
		$this->getLogger()->debug(Localizer::trans("plots-size", ["size" => 1 << Plots::$CHUNK_SIZE]));

		# attach Console object
		Members::attach(new FConsole());

		# Register engines
		$this->runEngines();

		# Load Integrations
		if (!$this->loadIntegrations()) {
			goto stop;
		}

		# Schedule update task
		$this->scheduleUpdateTask();

		# Schedule HUD task
		$this->scheduleHUDTask();

		# Run tests
		if (IN_DEV) {
			$this->runTests();
		}

		$this->generateCommandsInfoData();

		$this->getLogger()->debug(Localizer::trans("plugin.enabled"));
		return;
		stop:
		$this->getServer()->getPluginManager()->disablePlugin($this);
	}

	public function scheduleUpdateTask() {
		if (Gameplay::get("power.update-enabled", true)) {
			$this->getScheduler()->scheduleRepeatingTask(new PowerUpdateTask(), Gameplay::get("power.update-every", 10) * 20 * 60);
			$this->getLogger()->debug(Localizer::trans("plugin.power-update-enabled"));
		}
	}

	public function scheduleHUDTask() {
		if (Gameplay::get("hud.enabled", true)) {
			$this->getScheduler()->scheduleRepeatingTask(new HUD($this), 15);
			$this->getLogger()->debug(Localizer::trans("plugin.hud-enabled"));
		}
	}

	public function purgeInactiveFactions() {
		if ($this->getConfig()->get("purge-inactive-factions", true)) {
			$delta = $this->getConfig()->get("purge-after", 3600 * 24 * 7); // week
			foreach (Factions::getAll() as $faction) {
				if ($faction->isSpecial() or $faction->isPermanent()) {
					continue;
				}

				$lp = $faction->getLastOnline();
				if (time() - $lp > $delta) {
					$this->getLogger()->notice(Localizer::trans("log.purging-faction", [
						"faction"     => $faction->getName(),
						"last-online" => Text::ago($lp),
					]));
					$faction->disband(Faction::DISBAND_REASON_PURGE, true);
				}
			}
		}
	}

	/**
	 * @internal
	 */
	private function loadDataProvider(): bool {
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
			$this->getLogger()->critical(Localizer::trans('plugin.dataprovider-error', [$e->getMessage(), $e->getCode()]));
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}
		$this->getLogger()->debug(Localizer::trans('plugin.dataprovider-set', [$this->getDataProvider()->getName()]));
		return true;
	}

	public function getDataProvider(): DataProvider
    {
		return $this->dataProvider;
	}

	public function setDataProvider(DataProvider $provider) {
		$this->dataProvider = $provider;
	}

	/**
	 * @internal
	 */
	private function loadIntegrations(): bool{
		$stop = false;
		if ($this->economyEnabled()) {
			$n      = $this->getConfig()->get('economy-plugin', Economizer::DEFAULT_API);
			$plugin = $this->getServer()->getPluginManager()->getPlugin($n);
			if (!$plugin) {
				$this->getLogger()->error(Localizer::trans("economy-plugin-not-found", ["name" => $n]));
				$stop = true;
				goto end;
			}

			$t = Economizer::getTransistorFor($plugin);
			if (!$t) {
				$this->getLogger()->error(Localizer::trans("economy-plugin-not-supported", ["name" => $n]));
				$stop = true;
				goto end;
			}

			$this->economy = new Economizer($this, $t);
			if ($this->economy->ready()) {
                $this->getLogger()->debug(Localizer::trans('api-support-enabled', [
                    'api' => $this->economy->getName()
                ]));
			} else {
				$this->getLogger()->error(Localizer::trans("economy-not-ready", ["name" => $this->economy->getName()]));
			}
		}
		// If chat-formatter is set to false, then we assume that user is using PureChat
		if (!$this->getConfig()->get("force-chat-formatter")) {
			$pc = $this->getServer()->getPluginManager()->getPlugin("PureChat");
			if ($pc instanceof PureChat) {
				self::$engines["ChatEngine"]->setPureChat($pc);
				$this->getLogger()->debug(Localizer::trans("chat-formatter-set", [
					"plugin" => "PureChat",
				]));
			} else {
				$this->getLogger()->warning("PureChat not found! Safe fallback to built-in formatter");
				goto fallback_formatter;
			}
		} else {
			fallback_formatter:
			$this->getLogger()->debug(Localizer::trans("chat-formatter-set", [
				"plugin" => $this->getName() !== $this->getName() ?: Localizer::trans('built-in'),
			]));
		}
//		if ($this->getConfig()->get('enable-form-menus', true)) {
//			$fapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
//			if ($fapi && $fapi->isEnabled()) {
//				$this->formAPI = $fapi;
//			}
//			$this->getLogger()->debug(Localizer::trans("form-support-enabled", [
//				$this->isFormsEnabled() ? Localizer::trans("on") : Localizer::trans("off"),
//			]));
//		}
		end:
		return !$stop;
	}

	/**
	 * Returns true if economy support is turned on
	 */
	public function economyEnabled(): bool {
		return (bool) $this->getConfig()->get('economy-support', true);
	}

	/**
	 * @internal
	 */
	private function runEngines() {
		foreach (self::$registeredEngines as $k => $engine) {
			try {
				$class      = is_int($k) ? $engine : $k;
				$reflection = new \ReflectionClass($class);
				$shortName  = $reflection->getShortName();

				$this->getServer()->getPluginManager()->registerEvents($e = new $engine($this), $this);
				self::$engines[$shortName] = $e;
			} catch (\Exception $e) {
				$this->getLogger()->error("Error while initializing engine: " . $e->getMessage());
			}
		}

		if(Gameplay::get('scoreboard.enabled', true)) {
		    /** @var ScoreboardAPI $api */
		    $api = $this->getServer()->getPluginManager()->getPlugin("ScoreboardAPI");
		    if($api && $api->isEnabled()) {
                self::$engines["BoardEngine"] = $b = new BoardEngine($this, $api);
                $this->getServer()->getPluginManager()->registerEvents($b, $this);

                $this->getLogger()->debug(Localizer::trans('api-support-enabled', [
		            'api' => $api->getName()
                ]));
            }
        }
	}

	public function getEngine(string $name):  ? Engine {
		return self::$engines[$name];
	}

	/**
	 * @internal
	 */
	private function runTests() {
		$tests = glob($this->getFile() . "test/*_test.php");

		foreach ($tests as $test) {
			$this->getLogger()->info("Running " . $test . "");
			$code = file_get_contents($test);
			$code = substr($code, strpos($code, "<?php") + 5);
			try {
				eval($code);
			} catch (\Exception $e) {
				$this->getLogger()->error("Error while executing a test: " . $e->getMessage() . " on line " . $e->getLine());
				$this->getLogger()->info($e->getTraceAsString());
			}
		}

	}

	/**
	 * Generates a commands usage data list for Poggit
	 * @internal
	 */
	private function generateCommandsInfoData(string $file = null, bool $overwrite = false) {
		$file = $file ?? $this->getDataFolder() . "commands-usage.md";

		// Check if we can write this data to file
		if(file_exists($file) && !$overwrite) return;

		// Now lets generate that data
		$data = [];
		$fc = $this->getServer()->getCommandMap()->getCommand("faction");
		if(!$fc) return;

		$data = array_reverse($this->generateCommandInfoData($fc));
		$save = "```".PHP_EOL;
		foreach($data as $line) {
			$save .= $line . PHP_EOL;
		}
		$save .= "```";
		file_put_contents($file, $save);
	}

	private function generateCommandInfoData(Command $cmd) : array {
		$usage = [];
		if($cmd->isParent()) {
			foreach($cmd->getChilds() as $child) {
				$usage = array_merge($this->generateCommandInfoData($child), $usage);
			}
		}
		$usage[] = $cmd->getUsage();
		return $usage;
	}

	public function onDisable() {

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

	public function isFormsEnabled() : bool {
		return $this->getFormAPI() !== null;
	}

	public function getFormAPI():  ? FormAPI {
		return $this->formAPI;
	}

	/**
	 * @return Economizer|null
	 */
	public function getEconomy() :  ? Economizer {
		return $this->economy;
	}

	public function getFormHandler() : FactionForm {
		return $this->form;
	}

}
