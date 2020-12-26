<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\data\provider;

use fpe\data\FactionData;
use fpe\data\MemberData;
use fpe\entity\Faction;
use fpe\manager\Factions;
use fpe\manager\Plots;
use fpe\utils\Text;

class JSONDataProvider extends DataProvider
{
    use MemberFilePath, FactionFilePath;

    public function saveMember(MemberData $member)
    {
        $f = $this->getMemberFilePath($member, ".json");
        @mkdir(dirname($f));
        file_put_contents($f, self::json_encode($member->__toArray()));
    }

    public static function json_encode(array $data)
    {
        return Text::prettyPrint(json_encode($data));
    }

    public function saveFaction(FactionData $faction)
    {
        $f = $this->getFactionFilePath($faction, ".json");
        file_put_contents($f, self::json_encode($faction->__toArray()));
    }

    public function loadMember(string $name)
    {
        if (file_exists($f = $this->getMemberFilePath($name, ".json"))) {
            return new MemberData(json_decode(file_get_contents($f), true));
        }
        return null;
    }

    public function savePlots(array $plots)
    {
        file_put_contents($this->getMain()->getDataFolder() . "plots.json", self::json_encode($plots));
    }

    public function loadPlots()
    {
        if (!file_exists(($f = $this->getMain()->getDataFolder() . "plots.json"))) return;
        Plots::setPlots(json_decode(file_get_contents($f), true));
    }

    /**
     * @param string
     */
    public function deleteMember(string $identifier)
    {
        if (file_exists($f = $this->getMemberFilePath($identifier, ".json"))) {
            unlink($f);
        }
    }

    /**
     * @param string
     */
    public function deleteFaction(string $identifier)
    {
        if (file_exists($f = $this->getFactionFilePath($identifier, ".json"))) {
            unlink($f);
        }
    }

    public function loadFactions()
    {
        $special = [Faction::NONE, Faction::SAFEZONE, Faction::WARZONE];
        $files = glob($this->getMain()->getDataFolder() . "factions/*.json");
        $files = array_map(function ($el) {
            return substr($el, strpos($el, "factions/") + 9, -4);
        }, $files);
        foreach (DataProvider::order($files) as $faction) {
            $f = $this->loadFaction($faction);
            if ($f instanceof Faction) {
                Factions::attach($f);
            }
        }
    }

    public function loadFaction(string $id)
    {
        if (file_exists($f = $this->getFactionFilePath($id, ".json"))) {
            return new Faction($id, json_decode(file_get_contents($f), true));
        }
        return null;
    }

    public function saveFlags(array $flags)
    {
        $save = [];
        foreach ($flags as $flag) {
            $save[$flag->getId()] = $flag->__toArray();
        }
        file_put_contents($this->getFlagsFile(), self::json_encode($save));
    }

    public function getFlagsFile(): string
    {
        return $this->getMain()->getDataFolder() . "flags.json";
    }

    public function loadFlags()
    {
        if (file_exists($this->getFlagsFile())) {
            $data = file_get_contents($this->getFlagsFile());
            if (empty($data)) return;
            $flags = json_decode($data, true);
            foreach ($flags as $id => $flag) {
                $this->loadFlag($id, $flag);
            }
        }
    }

    public function loadPermissions()
    {
        if (file_exists($this->getPermsFile())) {
            $data = file_get_contents($this->getPermsFile());
            if (empty($data)) return;
            $perms = json_decode($data, true);
            foreach ($perms as $id => $perm) {
                $this->loadPermission($id, $perm);
            }
        }
    }

    public function getPermsFile(): string
    {
        return $this->getMain()->getDataFolder() . "permissions.json";
    }

    public function savePermissions(array $permissions)
    {
        $s = [];
        foreach ($permissions as $perm) {
            $s[$perm->getId()] = $perm->__toArray();
        }
        file_put_contents($this->getPermsFile(), self::json_encode($s));
    }

    public function close()
    {

    }

    public function getName(): string
    {
        return "JSON";
    }

    protected function prepare()
    {
        @mkdir($this->getMain()->getDataFolder() . "factions");
        @mkdir($this->getMain()->getDataFolder() . "members");
        @touch($this->getMain()->getDataFolder() . "flags.json");
        @touch($this->getMain()->getDataFolder() . "permissions.json");
    }

}
