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

namespace factions\data;


use factions\entity\Faction;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;

abstract class DataProvider
{

    /** @var DataProvider $instance */
    private static $instance;

    /** @var FactionsPE $plugin */
    protected $plugin;

    public function __construct(FactionsPE $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Load valid DataProvider
     *
     * @param FactionsPE $plugin
     * @param String $type
     * @return DataProvider|null
     */
    public static function load(FactionsPE $plugin, $type)
    {
        switch (strtolower($type)) {
            case 'nbt':
                self::$instance = new NBTDataProvider($plugin);
                break;
            case 'mysql':
                self::$instance = new MySQLDataProvider($plugin);
                break;
            case 'json':
                self::$instance = new JSONDataProvider($plugin);
                break;
            default:
                throw new \RuntimeException("No valid data provider loaded.");
                break;
        }
        self::get()->init();
        return self::get();
    }

    protected abstract function init();

    /**
     * Get choosed and loaded DataProvider
     *
     * @return DataProvider
     */
    public static function get() : DataProvider
    {
        return self::$instance;
    }

    public static function setDataProvider(DataProvider $provider)
    {
        self::$instance = $provider;
    }

    /**
     * @param $file
     * @param bool $create
     * @param string $default
     * @return string
     */
    public static function readFile($file, $create = false, $default = "") : string
    {
        $file = str_replace("__DATAFOLDER__", FactionsPE::getFolder(), $file);
        if (!file_exists($file)) {
            if ($create === true)
                file_put_contents($file, $default);
            else
                return $default;
        }
        return file_get_contents($file);
    }

    /**
     * @param $file
     * @param $data
     */
    public static function writeFile($file, $data){
        $file = str_replace("__DATAFOLDER__", FactionsPE::getFolder(), $file);
        $f = fopen($file, "w");
        if($f){
            fwrite($f, $data);
        }
        fclose($f);
    }

    public static function factionToArray(Faction $faction)
    {
        $players = [];
        if( $faction->getId() !== FactionsPE::FACTION_ID_NONE &&
            $faction->getId() !== FactionsPE::FACTION_ID_SAFEZONE &&
            $faction->getId() !== FactionsPE::FACTION_ID_WARZONE) {
            // Save faction player only if they are normal not special factions
            foreach ($faction->getPlayers() as $player) {
                $players[] = $player->getName();
            }
        }
        $data = [
            "name" => $faction->getName(),
            "id" => $faction->getId(),
            "isNone" => $faction->isNone(),
            "description" => $faction->getDescription(),
            "motd" => $faction->getMotd(),
            "createdAtMillis" => $faction->getCreatedAtMillis(),
            "powerBoost" => $faction->getPowerBoost(),
            "invitedPlayers" => $faction->getInvitedPlayers(),
            "relationWishes" => $faction->getRelationWishes(),
            "flags" => $faction->getFlags(),
            "perms" => $faction->getPerms(),
            "players" => $players
        ];
        if ($faction->hasHome()) {
            $home = $faction->getHome();
            $data["home"] = $home->x . ":" . $home->y . ":" . $home->z . ":" . $home->level->getName();
        }
        return $data;
    }

    public static function playerToArray(IFPlayer $player) : ARRAY
    {
        $data = [
            "name" => $player->getName(),
            "factionId" => $player->getFactionId(),
            "role" => $player->getRole(),
            "power" => $player->getPower(),
            "powerBoost" => $player->getPowerBoost(),
            "title" => $player->getTitle(),
            "default" => $player->isDefault(),
            "online" => $player->isOnline(),
            "lastPlayed" => $player->getLastPlayed(),
            "firstPlayed" => $player->getFirstPlayed()
        ];
        return $data;
    }

    public static function internalPriority($name, $ext = "") : int
    {
        switch(strtolower($name)) {
            case "wilderness".$ext:
                return 1;
            case "warzone".$ext:
                return 2;
            case "safezone".$ext:
                return 3;
            default: return PHP_INT_MAX;
        }
    }

    public function getPlugin() : FactionsPE
    {
        return $this->plugin;
    }


    /*** ABSTRACT FUNCTIONS ***/

    // Faction data
    public abstract function loadSavedFactions();

    public abstract function saveFactionData(Faction $faction, $async = false);

    public abstract function getSavedFactionData($name) : ARRAY;

    public abstract function deleteFactionData(Faction $faction) : BOOL;

    // Player data
    public abstract function getSavedPlayerData($name) : ARRAY;

    public abstract function savePlayerData(IFPlayer $player, $async = false);


}
