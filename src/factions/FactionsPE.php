<?php
/* 
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */
namespace factions;

use factions\{
    command\FactionCommand,
    data\DataProvider, 
    data\NBTDataProvider, 
    engine\StatsEngine,
    engine\ChatEngine,
    engine\CombatEngine,
    engine\ExploitEngine,
    engine\PlayerEngine,
    engine\MainEngine,
    entity\Flag, 
    entity\FPlayer, 
    entity\FConsole,
    entity\Perm, 
    integrations\Economy, 
    mixin\PowerMixin, 
    objs\Factions, 
    objs\Plots, 
    objs\Rel, 
    predicate\Predicate, 
    task\HUDTask, 
    task\PowerUpdateTask, 
    utils\Settings, 
    utils\Text
};
use pocketmine\{
    plugin\PluginBase, utils\TextFormat
};
use evalcore\{
    EvalCore, engine\Engines
};
use ssl\LibLoader;

class FactionsPE extends PluginBase
{

    # Perms
    const ACCESS = "factions.access";
    const ACCESS_VIEW = "factions.access.view";
    const ACCESS_PLAYER = "factions.access.player";
    const ACCESS_FACTION = "factions.access.faction";
    const OVERRIDE = "factions.override";
    const CLAIM = "factions.claim";
    const CLAIM_ONE = "factions.claim.one";
    const CLAIM_AUTO = "factions.claim.auto";
    const CLAIM_FILL = "factions.claim.fill";
    const CLAIM_SQUARE = "factions.claim.square";
    const CLAIM_CIRCLE = "factions.claim.circle";
    const CLAIM_ALL = "factions.claim.all";
    const CLOSE = "factions.close";
    const CREATE = "factions.create";
    const DESCRIPTION = "factions.description";
    const DISBAND = "factions.disband";
    const EXPANSIONS = "factions.expansions";
    const FACTION = "factions.faction";
    const FLAG = "factions.flag";
    const FLAG_LIST = "factions.flag.list";
    const FLAG_SET = "factions.flag.set";
    const FLAG_SHOW = "factions.flag.show";
    const HOME = "factions.home";
    const INVITE = "factions.invite";
    const INVITE_LIST = "factions.invite.list";
    const INVITE_LIST_OTHER = "factions.invite.list.other";
    const INVITE_ADD = "factions.invite.add";
    const INVITE_REMOVE = "factions.invite.remove";
    const JOIN = "factions.join";
    const JOIN_OTHERS = "factions.join.others";
    const KICK = "factions.kick";
    const LEAVE = "factions.leave";
    const LEADER = "factions.leader"; # Lang
    const LIST = "factions.list";
    const MAIN = "factions.main";
    const MAP = "factions.map";
    const MONEY = "factions.money";
    const MONEY_BALANCE = "factions.money.balance";
    const MONEY_BALANCE_ANY = "factions.money.balance.any";
    const MONEY_DEPOSIT = "factions.money.deposit";
    const MONEY_F2F = "factions.money.f2f";
    const MONEY_F2P = "factions.f2p";
    const MONEY_P2F = "factions.p2f";
    const MONEY_WITHDRAW = "factions.money.withdraw";
    const MOTD = "factions.motd";
    const OPEN = "factions.open";
    const PERM = "factions.perm";
    const PERM_LIST = "factions.perm.list";
    const PERM_SET = "factions.perm.set";
    const PERM_SHOW = "factions.perm.show";
    const PLAYER = "factions.player";
    const POWERBOOST = "factions.powerboost";
    const RANK = "factions.rank";
    const RANK_SHOW = "factions.rank.show";
    const RANK_ACTION = "factions.rank.action";
    const RELATION = "factions.relation";
    const RELATION_SET = "factions.relation.set";
    const RELATION_LIST = "factions.relation.list";
    const RELATION_WISHES = "factions.relation.wishes";
    const SEECHUNK = "factions.seechunk";
    const SEECHUNKOLD = "factions.seechunkold";
    const SETHOME = "factions.sethome";
    const SETPOWER = "factions.setpower";
    const STATUS = "factions.status";
    const NAME = "factions.name";
    const TITLE = "factions.title";
    const TITLE_COLOR = "factions.title.color";
    const TERRITORYTITLES = "factions.territorytitles";
    const UNCLAIM = "factions.unclaim";
    const UNCLAIM_ONE = "factions.unclaim.one";
    const UNCLAIM_AUTO = "factions.unclaim.auto";
    const UNCLAIM_FILL = "factions.unclaim.fill";
    const UNCLAIM_SQUARE = "factions.unclaim.square";
    const UNCLAIM_CIRCLE = "factions.unclaim.circle";
    const UNCLAIM_ALL = "factions.unclaim.all";
    const UNSETHOME = "factions.unsethome";
    const UNSTUCK = "factions.unstuck";
    const VERSION = "factions.version";

    # Note to myself: this class isn't intended to be inherited
    const FACTION_ID_SAFEZONE = "safezone";
    const FACTION_ID_NONE = "none";
    const FACTION_ID_WARZONE = "warzone";
    const NAME_NONE_DEFAULT = "Wilderness";
    const NAME_SAFEZONE_DEFAULT = "Safezone";
    const NAME_WARZONE_DEFAULT = "Warzone";

    private static $disabling = false;
    private static $instance;

    /** @var float $start */
    public $start;
    
    /** @var DataProvider $data */
    private $data;
    
    // --------------------------------------------------------
    // STATIC
    // --------------------------------------------------------

    public static function getFolder() : string
    {
        return self::get()->getDataFolder();
    }

    public static function get() : FactionsPE
    {
        return self::$instance;
    }

    public static function isShuttingDown() : bool
    {
        return self::$disabling;
    }

    public function onLoad()
    {
        $this->start = microtime(true);
        LibLoader::loadLib($this->getFile()."lib/Localizer");
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "logs");
        Localizer::transferLanguages($this->getFile()."resources/languages", $this->getDataFolder()."languages");
        $this->saveDefaultConfig();
        $this->settings = new Settings($this->getDataFolder()."config.yml", Settings::YAML);
        Perm::init();
        Flag::init();
        Predicate::init();
    }

    public function onEnable()
    {
        Localizer::loadLanguages($this->getDataFolder()."languages");
        try {
            $this->data = DataProvider::load($this, $this->getConfig()->get('data-provider', 'NBT'));
        } catch (\Exception $e) {
            $this->getLogger()->critical("Failed to load valid data provider. Error: " . $e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        // Init classes
        foreach([PowerMixin::class, Plots::class => true, Economy::class => true, Rel::class] as $a => $b) {
            if(is_int($a)){ new $b(); continue; }
            new $a($this);
        }
        // TODO: Make better factions manager
        new Factions();

        // Start hud task if necessary
        if ($this->getConfig()->get('enable-hud', true)) {
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new HUDTask($this), 10);
            $this->getLogger()->info(Text::parse('plugin.log.hud.enabled'));
        }
        foreach([StatsEngine::class, ChatEngine::class, ExploitEngine::class, CombatEngine::class, MainEngine::class,
            PlayerEngine::class] as $engine) {
            Engines::registerEngine($engine);
        }


        # Register command
        $this->getServer()->getCommandMap()->register('FactionsPE', new FactionCommand($this));

        # Start Power update task
        $interval = Settings::get("power.updateInterval", 600) * 20; // Default: 10 
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PowerUpdateTask($this, $interval), $interval);
        
        FPlayer::attach(new FConsole());
        
        $this->getLogger()->info(Text::parse('plugin.log.enable'));
    }

    public function onDisable()
    {
        self::$disabling = true;
        foreach($this->getServer()->getOnlinePlayers() as $player) $player->kick("Server shutting down");
        if (Factions::get() instanceof Factions) Factions::close();
        if (Plots::get() instanceof Plots) Plots::close();
        FPlayer::saveAll();

        $this->getLogger()->info(Localizer::trans('plugin.disabled'));
    }

    /**
     * @return PowerMixin
     */
    public function getPowerMixin() : PowerMixin
    {
        return PowerMixin::get();
    }

}
