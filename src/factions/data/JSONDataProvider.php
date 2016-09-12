<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/27/16
 * Time: 5:05 PM
 */

namespace factions\data;


use factions\entity\Faction;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\objs\Factions;

class JSONDataProvider extends DataProvider
{


    private static $playerFolder;
    private static $factionFolder;

    /*** ABSTRACT FUNCTIONS ***/
    public function loadSavedFactions()
    {
        $factions = $this->order(scandir(self::$factionFolder));
        var_dump($factions);
        if(in_array("wilderness.json", $factions, true)) {
            if ($factions[0] !== "wilderness.json") {
                throw new \RuntimeException("Wilderness must be loaded as first faction");
            }
        }

        $this->getPlugin()->getLogger()->info("Loading ".(count($factions) - 2)." saved factions...");
        foreach($factions as $f) {
            if($f == '.' or $f == '..'){ continue; }
            $data = $this->getSavedFactionData($f);
            if (empty($data)) {
                $this->getPlugin()->getLogger()->warning("Failed to load faction: " . $f);
                continue;
            }
            Factions::attach(new Faction($data["name"], $data["id"], $data));

            if (Factions::getById($data["id"])) {
                $this->getPlugin()->getLogger()->info("Faction " . $data["name"] . " loaded!");
                continue;
            } else {
                $this->getPlugin()->getLogger()->info("Unknown error occured while loading faction: " . $data["name"]);
                continue;
            }
        }
    }

    public function getSavedFactionData($name) : ARRAY
    {
        $file = self::$factionFolder . "/" . $name;
        if (file_exists($file)) {
            $raw = DataProvider::readFile($file, false);
            $ret = json_decode($raw, true);
            if ($ret === null or !$ret) {
                $this->getPlugin()->getLogger()->warning("Corrupted faction data: " . $name);
                return [];
            }
            return $ret;
        } else {
            $this->getPlugin()->getLogger()->warning("Faction file '$file' does not exist!");
        }
        return [];
    }

    public function saveFactionData(Faction $faction, $async = false) # TODO: Async implentation
    {
        $data = DataProvider::factionToArray($faction);
        if (!empty($data) && $data !== NULL) {
            # As we have verified the data it's time for us to save it
            DataProvider::writeFile(self::$factionFolder . "/" . trim(strtolower($faction->getName())).".json", json_encode($data));
        }
    }

    public function deleteFactionData(Faction $faction) : BOOL
    {
        // TODO: Implement deleteFactionData() method.
    }
    
    public function getSavedPlayerData($name) : ARRAY
    {
        $file = self::$playerFolder . "/" . strtolower(trim($name)) . ".json";
        if (file_exists($file)) {
            $raw = DataProvider::readFile($file, false);
            $ret = json_decode($raw, true);
            if ($ret === false) return [];
            return $ret;
        }
        return [];
    }

    public function savePlayerData(IFPlayer $player, $async = false) # TODO: Async implentation
    {
        $file = self::$playerFolder . "/" . strtolower(trim($player->getName())) . ".json";
        $data = DataProvider::playerToArray($player);
        if(!empty($data) && $data !== NULL) {
            DataProvider::writeFile($file, json_encode($data));
        }
    }

    protected function init()
    {
        self::$factionFolder = FactionsPE::getFolder() . "factions";
        self::$playerFolder = FactionsPE::getFolder() . "players";

        @mkdir(self::$factionFolder);
        @mkdir(self::$playerFolder);
    }

    public function order(array $factions) {
        usort($factions, function($a, $b){
            $pa = DataProvider::internalPriority($a, ".json");
            $pb = DataProvider::internalPriority($b, ".json");
            if($pa !== $pb) return $pa <=> $pb;
            return $a <=> $b;
        });
            return $factions;
    }
}