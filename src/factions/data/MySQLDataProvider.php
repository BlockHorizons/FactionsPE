<?php
namespace factions\data;

use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\task\MySQLPingTask;
use factions\utils\Text;
use pocketmine\nbt\tag\CompoundTag;

class MySQLDataProvider extends DataProvider
{

    const QUERY_GET_MEMBERS = "SELECT * FROM `members` WHERE `id` = '{M_ID}';";
    const QUERY_GET_ALL_FACTIONS = "SELECT * FROM `factions`;";
    const QUERY_GET_LAST_MEMBERS_ID = "SELECT MAX(m_id) FROM `factions` AS m_id;";
    const QUERY_NEW_FACTION = "INSERT INTO `factions` (%s) VALUES (%s);";
    const QUERY_SAVE_FACTION = "UPDATE `factions` SET %s WHERE `id` = '{FACTION_ID}'";
    const QUERY_GET_FACTION = "SELECT * FROM `factions` WHERE `name` = '{FACTION}';";
    const QUERY_NEW_MEMBER = "INSERT INTO `members` (%s) VALUES (%s);";
    const QUERY_SAVE_MEMBER = "UPDATE `members` SET %s WHERE `player` = {NAME}";
    const QUERY_DEL_MEMBER = "DELETE FROM `members` WHERE `id` = {M_ID}";
    const QUERY_DEL_FACTION = "DELETE FROM `factions` WHERE `id` = {FACTION_ID}";

    /** @var \mysqli $database */
    protected $database;

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin);
        $credentials = $this->plugin->getConfig()->get("mysql-settings");
        if (!isset($credentials["host"]) or !isset($credentials["user"]) or !isset($credentials["password"]) or !isset($credentials["database"])) {
            $this->plugin->getLogger()->critical("Invalid MySQL settings");
            parent::setDataProvider(new DummyDataProvider($this->plugin));
            return;
        }
        $plugin->getLogger()->info(Text::parse("plugin.log.mysql.connecting"));
        $start = microtime(true);
        $this->database = new \mysqli($credentials["host"], $credentials["user"], $credentials["password"], $credentials["database"], isset($credentials["port"]) ? $credentials["port"] : 3306);
        if ($this->database->connect_error) {
            $this->plugin->getLogger()->critical("Couldn't connect to MySQL: " . $this->database->connect_error);
            parent::setDataProvider(new DummyDataProvider($this->plugin));
            return;
        }

        /*** SETUP ***/
        $members_query = $plugin->getResource("members.sql");
        $factions_query = $plugin->getResource("factions.sql");

        $this->query(stream_get_contents($members_query));
        $this->query(stream_get_contents($factions_query));

        fclose($members_query);
        fclose($factions_query);

        //

        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLPingTask($this->plugin, $this->database), 600); //Each 30 seconds
        $plugin->getLogger()->info(Text::parse('plugin.log.mysql.connected', (microtime(true) - $start)));
    }

    public function query(string $query)
    {
        $r = $this->database->query($query);
        if ($this->database->error) {
            throw new \RuntimeException("Query ($query) failed " . $this->database->errno . "#: " . $this->database->error);
        }
        if (is_bool($r)) return $r;
        if ($r->num_rows > 0) {
            $d = [];
            for ($row = 1; $row <= $r->num_rows; $row++) {
                $d[] = $r->fetch_assoc();
            }
            return $d;
        }
        return $r->fetch_assoc();
    }

    public static function insertValues($query, array $values)
    {
        return sprintf(
            $query,
            implode(',', array_keys($values)),
            rtrim("\"" . implode('","', array_values($values)), ",\"") . "\""
        );
    }

    public static function insertSets($query, array $values)
    {
        $sets = "";
        foreach ($values as $key => $value) {
            $sets .= "$key='$value', ";
        }
        return sprintf($query, rtrim($sets, ", "));
    }

    public function loadSavedFactions()
    {
        # TODO
    }

    /***
     * @param Faction $faction
     * @param bool $async
     */
    public function saveFactionData(Faction $faction, $async = false)
    {
        # TODO
    }

    public function getSavedFactionData($name) : CompoundTag
    {
        # TODO
    }

    public function deleteFactionData(Faction $faction) : BOOL
    {
        return $this->query(str_replace("{FACTION_ID}", $faction->getId(), self::QUERY_DEL_FACTION));
    }

    public function getM_ID($faction) : int
    {
        $r = $this->query("SELECT `m_id` FROM `factions` WHERE `name` = '$faction';");
        if ($r !== NULL) return $r[0]['m_id'];
        # Faction doesn't exist and we need to create new members row for it
        $lastId = $this->query(self::QUERY_GET_LAST_MEMBERS_ID);
        if ($lastId === NULL) return 1;
        $id = ($lastId["MAX(m_id)"] + 1);
        do {
            $taken = true;
            if ($this->query("SELECT * FROM `members` WHERE `id` = $id;") !== NULL) {
                $taken = false;
                $id++;
            }
        } while (!$taken);
        return $id;
    }

    public function getSavedPlayerData($name) : ARRAY
    {
        // TODO: Implement getSavedPlayerData() method.
    }

    public function savePlayerData(FPlayer $player, $async = false)
    {
        // TODO: Implement savePlayerData() method.
    }

    private function connect($host, $user, $database, $password, $port = 3306)
    {
        try {
            $this->database = new \mysqli($host, $user, $password, $database, $port);
            if ($this->database->connect_error) {
                throw new \Exception("Failed to connect to database " . $this->database->connect_errno . "#: " . $this->database->connect_error);
            } else {
                $this->getPlugin()->getLogger()->info("Connected to MySQL database successfully.");
            }
        } catch (\Exception $e) {
            $this->getPlugin()->getLogger()->critical("Failed to start MySQL DataProvider: " . $e->getMessage());
        }
    }

    public function getPlugin() : FactionsPE
    {
        return $this->plugin;
    }
}