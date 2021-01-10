<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\data\provider;

use BlockHorizons\FactionsPE\data\FactionData;
use BlockHorizons\FactionsPE\data\MemberData;
use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\entity\Plot;
use BlockHorizons\FactionsPE\manager\Factions;
use BlockHorizons\FactionsPE\manager\Plots;

# Our best is yet to come (Howling at the moon)

class YAMLDataProvider extends DataProvider
{
    use MemberFilePath, FactionFilePath;

    public function saveMember(MemberData $member)
    {
        $f = $this->getMemberFilePath($member, ".yml");
        @mkdir(dirname($f));
        file_put_contents($f, yaml_emit($member->__toArray()));
    }

    public function saveFaction(FactionData $faction)
    {
        $f = $this->getFactionFilePath($faction, ".yml");
        file_put_contents($f, yaml_emit($faction->__toArray()));
    }

    public function loadMember(string $name)
    {
        if (file_exists($f = $this->getMemberFilePath($name, ".yml"))) {
            return new MemberData(yaml_parse(file_get_contents($f)));
        }
        return null;
    }

    public function savePlots(array $plots)
    {
        file_put_contents($this->getMain()->getDataFolder() . "plots.yml", yaml_emit($plots));
    }

    public function loadPlots()
    {
        if (!file_exists(($f = $this->getMain()->getDataFolder() . "plots.yml"))) {
            return;
        }

        Plots::setPlots(yaml_parse_file($f));
    }

    /**
     * @param string
     */
    public function deleteMember(string $identifier)
    {
        if (file_exists($f = $this->getMemberFilePath($identifier, ".yml"))) {
            unlink($f);
        }
    }

    /**
     * @param string
     */
    public function deleteFaction(string $identifier)
    {
        if (file_exists($f = $this->getFactionFilePath($identifier, ".yml"))) {
            unlink($f);
        }
    }

    public function loadFactions()
    {
        $files = array_merge(
            glob($this->getMain()->getDataFolder() . "factions/*.yml"),
            glob($this->getMain()->getDataFolder() . "factions/*.yaml")
        );
        $files = array_map(function ($el) {
            return substr(basename($el), 0, strpos($el, '.yml') !== false ? -4 : -5);
        }, $files);
        foreach (DataProvider::order($files) as $faction) {
            $f = $this->loadFaction($faction);
            if ($f instanceof Faction) {
                Factions::attach($f);
            }
        }
    }

    public function loadFaction(string $id): ?Faction
    {
        if (file_exists($f = $this->getFactionFilePath($id, ".yml")) || file_exists($f = $this->getFactionFilePath($id, ".yaml"))) {
            $raw = yaml_parse_file($f);
            if($raw === null) {
                $this->getMain()->getLogger()->notice("Faction '$id' YAML data is corrupted/malformed");
                return null;
            }
            $data = $this->__loadFaction($id, $raw);
            if (!$data) return null;

            return new Faction($id, $data);
        }
        return null;
    }

    public function saveFlags(array $flags)
    {
        $save = [];
        foreach ($flags as $flag) {
            $save[$flag->getId()] = $flag->__toArray();
        }
        file_put_contents($this->getFlagsFile(), yaml_emit($save));
    }

    public function getFlagsFile(): string
    {
        return $this->getMain()->getDataFolder() . "flags.yml";
    }

    public function loadFlags()
    {
        if (file_exists($this->getFlagsFile())) {
            $data = file_get_contents($this->getFlagsFile());
            if (empty($data)) {
                return;
            }

            $flags = yaml_parse($data);
            foreach ($flags as $id => $flag) {
                $this->loadFlag($id, $flag);
            }
        }
    }

    public function loadPermissions()
    {
        if (file_exists($this->getPermsFile())) {
            $data = file_get_contents($this->getPermsFile());
            if (empty($data)) {
                return;
            }

            $perms = yaml_parse($data);
            foreach ($perms as $id => $perm) {
                $this->loadPermission($id, $perm);
            }
        }
    }

    public function getPermsFile(): string
    {
        return $this->getMain()->getDataFolder() . "permissions.yml";
    }

    public function savePermissions(array $permissions)
    {
        $s = [];
        foreach ($permissions as $perm) {
            $s[$perm->getId()] = $perm->__toArray();
        }
        file_put_contents($this->getPermsFile(), yaml_emit($s));
    }

    public function close()
    {

    }

    public function getName(): string
    {
        return "YAML";
    }

    /**
     * @param Plot $plot
     * @return void
     */
    public function deletePlot(Plot $plot)
    {
    }

    protected function prepare()
    {
        @mkdir($this->getMain()->getDataFolder() . "factions");
        @mkdir($this->getMain()->getDataFolder() . "members");
        @touch($this->getMain()->getDataFolder() . "flags.yml");
        @touch($this->getMain()->getDataFolder() . "permissions.yml");
    }

}
