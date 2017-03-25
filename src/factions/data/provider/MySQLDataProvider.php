<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.24.3
 * Time: 18:52
 */

namespace factions\data\provider;

use factions\data\FactionData;
use factions\data\MemberData;
use factions\entity\Member;
use factions\entity\Plot;
use factions\flag\Flag;
use factions\manager\Plots;
use factions\permission\Permission;
use factions\task\MySQLPinger;

class MySQLDataProvider extends DataProvider
{

    /** @var  int */
    public $connection_time;

    /** @var \mysqli */
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
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    public function query(string $query)
    {
        if (!$this->isConnected()) {
            throw new \Exception("can not query while not connected");
        }
        $r = $this->connection->query($query);
        if ($this->connection->error) {
            throw new \Exception("Query error: " . $this->connection->error . " (query: $query)", $this->connection->errno);
        }
        return $r;
    }

    public function isConnected(): bool
    {
        return $this->connection instanceof \mysqli;
    }

    public function saveFaction(FactionData $faction)
    {
        $this->query($this->gen_ins_query([
            'name' => $faction->getName(),
            'id' => $faction->getId(),
            'createdAt' => $faction->getCreatedAt(),
            'description' => $faction->getDescription(),
            'motd' => $faction->getMotd(),
            'powerBoost' => $faction->getPowerBoost(),
            'bank' => $faction->getBank(),
            'perms' => $faction->getPermissions(),
            'flags' => $faction->getFlags(),
            'relationWishes' => $faction->getRelationWishes(),
            'invitedPlayers' => $faction->getInvitedPlayers(),
            'members' => $faction->getRawMembers()
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
            return new MemberData($r->fetch_assoc());
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
                'fid' => $fid
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
                'standard' => $flag->isStandard()
            ], 'flags'));
        }
    }

    public function loadPermissions()
    {
        if (($r = $this->query("SELECT * FROM `permissions`;"))) {
            while ($data = $r->fetch_assoc()) {
                $data["standard"] = json_decode($data["standard"]);
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
                'priority' => $p->getPriority()
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
     * @return \mysqli
     */
    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    public function loadFactions()
    {
        if (($r = $this->query("SELECT `id` FROM `factions`;"))) {
            while ($data = $r->fetch_assoc()) {
                $this->loadFaction($data["id"]);
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
            $data["perms"] = json_decode($data["perms"]);
            $data["flags"] = json_decode($data["flags"]);
            $data["relationWishes"] = json_decode($data["relationWishes"]);
            $data["members"] = json_decode($data["members"]);
            return new FactionData($data);
        }
        return null;
    }

    protected function prepare()
    {
        $st = microtime(true);
        $d = $this->getMain()->getConfig()->get("mysql");

        $this->connection = @new \mysqli($d["host"], $d["user"], $d["password"], $d["database"], $d["port"]);
        if ($this->connection->connect_error) {
            throw new \Exception($this->connection->connect_error, $this->connection->connect_errno);
        }
        $this->getMain()->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLPinger($this->connection), 600);
        $this->connection_time = microtime(true) - $st;
    }

    /**
     * @param Plot $plot
     * @return void
     */
    public function deletePlot(Plot $plot)
    {
        $this->query("DELETE * FROM `plots` WHERE `pos`='".$plot->hash()."'");
    }

}