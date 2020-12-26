<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.24.3
 * Time: 18:52
 */

namespace fpe\data\provider;

use Exception;
use fpe\data\FactionData;
use fpe\data\MemberData;
use fpe\entity\Faction;
use fpe\entity\Member;
use fpe\entity\Plot;
use fpe\flag\Flag;
use fpe\manager\Factions;
use fpe\manager\Plots;
use fpe\permission\Permission;
use fpe\task\MySQLPinger;
use mysqli;
use mysqli_result;

class MySQLDataProvider extends DataProvider
{

    /** @var  int */
    public $connection_time;

    /** @var mysqli */
    private $connection;

    public function saveMember(MemberData $member)
    {
        /** @var Member $member */
        $query = "INSERT INTO `members` (name, title, firstPlayed, lastPlayed, power) VALUES ('{$member->getName()}', '{$member->getTitle()}', {$member->getFirstPlayed()}, {$member->getLastPlayed()},
        {$member->getPower()});";
        $this->query($query);
    }

    /**
     * @param string $query
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function query(string $query)
    {
        if (!$this->isConnected()) {
            throw new Exception("can not query while not connected");
        }
        $r = $this->connection->query($query);
        if ($this->connection->error) {
            throw new Exception("Query error: " . $this->connection->error . " (query: $query)", $this->connection->errno);
        }
        return $r;
    }

    public function isConnected(): bool
    {
        return $this->connection instanceof mysqli;
    }

    public function saveFaction(FactionData $faction)
    {
        $this->query($this->gen_ins_query([
            'name' => $faction->getName(),
            'id' => $faction->getId(),
            'createdAt' => $faction->getCreatedAt(),
            'timeOnline' => $faction->getOnlineTime(),
            'description' => $faction->getDescription(),
            'motd' => $faction->getMotd(),
            'powerBoost' => $faction->getPowerBoost(),
            'bank' => $faction->getBank(),
            'perms' => $faction->getPermissions(),
            'flags' => $faction->getFlags(),
            'relationWishes' => $faction->getRelationWishes(),
            'invitedPlayers' => $faction->getInvitedPlayers(),
            'members' => $faction->getRawMembers(),
        ], 'factions'));
    }

    public function gen_ins_query(array $values, string $table, bool $update = true): string
    {
        /** @noinspection SqlResolve */
        return "INSERT INTO `$table` (" . implode(", ", array_map(function ($el) {
                return "`$el`";
            }, array_keys($values))) . ") VALUES (" . implode(", ", array_map(function ($el) {
                if (is_array($el)) {
                    return "'" . $this->connection->escape_string(json_encode($el)) . "'";
                } elseif (is_bool($el)) {
                    return "'" . ($el ? "1" : "0") . "'";
                } elseif (is_int($el) || is_float($el)) {
                    return $el;
                } else {
                    return "\"" . $this->connection->escape_string($el) . "\"";
                }
            }, array_values($values))) . ")" . ($update ? " ON DUPLICATE KEY UPDATE " . $this->pair_vk($values) . ";" : ";");
    }

    public function pair_vk(array $values): string
    {
        $r = "";
        foreach ($values as $key => $value) {
            $r .= "$key=" . $this->to_sql($value) . ", ";
        }
        $r = rtrim($r, ", ");
        return $r;
    }

    public function to_sql($value): string
    {
        if (is_array($value)) {
            return "'" . $this->connection->escape_string(json_encode($value)) . "'";
        } elseif (is_bool($value)) {
            return "'" . ($value ? "1" : "0") . "'";
        } elseif (is_int($value) || is_float($value)) {
            return $value;
        } else {
            return "'" . $this->connection->escape_string($value) . "'";
        }
    }

    /**
     * @param string $name
     * @return MemberData|null
     */
    public function loadMember(string $name)
    {
        if (($r = $this->query("SELECT * FROM `members` WHERE `name`='$name';"))) {
            if (!empty($data = $r->fetch_assoc())) {
                return new MemberData($data);
            }
        }
        return null;
    }

    /**
     * @param string
     */
    public function deleteMember(string $identifier)
    {
        $this->query("DELETE * FROM `members` WHERE `name`='$identifier';");
    }

    /**
     * @param string
     */
    public function deleteFaction(string $identifier)
    {
        $this->query("DELETE * FROM `factions` WHERE `id`='$identifier';");
    }

    public function savePlots(array $plots)
    {
        foreach ($plots as $pos => $fid) {
            $this->query($this->gen_ins_query([
                'pos' => $pos,
                'fid' => $fid,
            ], 'plots'));
        }
    }

    /**
     * Must set plots using Plots::setPlots()
     */
    public function loadPlots()
    {
        if (($r = $this->query("SELECT * FROM `plots`;"))) {
            $plots = [];
            while ($data = $r->fetch_assoc()) {
                $plots[$data["pos"]] = $data["fid"];
            }
            Plots::setPlots($plots);
        }
    }

    public function loadFlags()
    {
        if (($r = $this->query("SELECT * FROM `flags`;"))) {
            while ($data = $r->fetch_assoc()) {
                $data["desc"] = $data["description"];
                $data["descYes"] = $data["descriptionYes"];
                $data["descNo"] = $data["descriptionNo"];
                $this->loadFlag($data["id"], $data);
            }
        }
    }

    /**
     * @param Flag[] $flags
     */
    public function saveFlags(array $flags)
    {
        foreach ($flags as $flag) {
            $this->query($this->gen_ins_query([
                'id' => $flag->getId(),
                'name' => $flag->getName(),
                'priority' => $flag->getPriority(),
                'description' => $flag->getDesc(),
                'descriptionYes' => $flag->getDescYes(),
                'descriptionNo' => $flag->getDescNo(),
                'visible' => $flag->isVisible(),
                'editable' => $flag->isEditable(),
                'standard' => $flag->isStandard(),
            ], 'flags'));
        }
    }

    public function loadPermissions()
    {
        if (($r = $this->query("SELECT * FROM `permissions`;"))) {
            while ($data = $r->fetch_assoc()) {
                $data["standard"] = json_decode($data["standard"], true);
                $this->loadPermission($data["name"], $data);
            }
        }
    }

    /**
     * @param Permission[] $permissions
     */
    public function savePermissions(array $permissions)
    {
        foreach ($permissions as $p) {
            $this->query($this->gen_ins_query([
                'name' => $p->getName(),
                'description' => $p->getDescription(),
                'standard' => $p->getStandard(),
                'territory' => $p->isTerritory(),
                'editable' => $p->isEditable(),
                'visible' => $p->isVisible(),
                'priority' => $p->getPriority(),
            ], 'permissions'));
        }
    }

    public function close()
    {
        $this->connection->close();
    }

    public function getName(): string
    {
        return "MySQL" . ($this->isConnected() ? " (Host: " . $this->connection->host_info . ", " . $this->connection_time . "s)" : "");
    }

    /**
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function loadFactions()
    {
        if (($r = $this->query("SELECT `id` FROM `factions`;"))) {
            while ($data = $r->fetch_assoc()) {
                $f = $this->loadFaction($data["id"]);
                if ($f instanceof Faction) {
                    Factions::attach($f);
                }
            }
        }
    }

    /**
     * @param string $id
     * @return FactionData|null
     */
    public function loadFaction(string $id)
    {
        if (($r = $this->query("SELECT * FROM `factions` WHERE `id`='$id';"))) {
            $data = $r->fetch_assoc();
            $data["perms"] = json_decode($data["perms"], true);
            $data["flags"] = json_decode($data["flags"], true);
            $data["relationWishes"] = json_decode($data["relationWishes"], true);
            $data["members"] = json_decode($data["members"], true);
            $data["invitedPlayers"] = json_decode($data["invitedPlayers"], true);
            return new Faction($id, $data);
        }
        return null;
    }

    /**
     * @param Plot $plot
     * @return void
     */
    public function deletePlot(Plot $plot)
    {
        $this->query("DELETE * FROM `plots` WHERE `pos`='" . $plot->hash() . "'");
    }

    protected function prepare()
    {
        $st = microtime(true);
        $d = $this->getMain()->getConfig()->getNested("data-provider.mysql");

        $this->connection = @new mysqli($d["host"], $d["user"], $d["password"], "", $d["port"]);
        $this->getMain()->getScheduler()->scheduleRepeatingTask(new MySQLPinger($this->connection), 600);
        $this->connection_time = microtime(true) - $st;

        if ($this->connection->connect_error) {
            throw new Exception($this->connection->connect_error, $this->connection->connect_errno);
        } else {
            try {
                $this->query("CREATE DATABASE " . $d["database"] . ";");
            } catch (Exception $e) {
                if ($e->getCode() !== 1007) {
                    // Ignore
                    throw $e;
                }
            }
        }
        $this->query("USE " . $d["database"] . "");

        $query = stream_get_contents($h = $this->getMain()->getResource("mysql.sql"));
        @fclose($h);
        foreach (explode("CREATE", $query) as $st) {
            if (empty($st)) {
                continue;
            }

            try {
                $this->query("CREATE" . $st);
            } catch (Exception $e) {
                if ($e->getCode() !== 1050) {
                    // Ignore
                    throw $e;
                }
            }
        }

    }

}
