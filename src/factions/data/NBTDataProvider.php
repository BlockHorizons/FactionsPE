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
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\FileWriteTask;

class NBTDataProvider extends DataProvider
{

    private static $factionsDataFolder;

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin);
        self::$factionsDataFolder = $plugin->getDataFolder()."factions/";

        @mkdir(self::$factionsDataFolder);
    }

    /**
     * @param string   $name
     * @param CompoundTag $nbtTag
     * @param bool $async
     */
    public function saveFactionData($name, CompoundTag $nbtTag, $async = false)
    {
        $nbt = new NBT(NBT::BIG_ENDIAN);
        try {
            $nbt->setData($nbtTag);

            if ($async) {
                $this->getPlugin()->getServer()->getScheduler()->scheduleAsyncTask(new FileWriteTask(self::$factionsDataFolder . strtolower($name) . ".dat", $nbt->writeCompressed()));
            } else {
                file_put_contents(self::$factionsDataFolder . strtolower($name) . ".dat", $nbt->writeCompressed());
            }
        } catch (\Throwable $e){
            //$this->plugin->logError($e);
        }
    }

    public function loadSavedFactions()
    {
        $files = scandir(self::$factionsDataFolder);
        foreach ($files as $file) {
            if (substr($file, -4) === '.dat') {
                $nbt = $this->getSavedFactionData(substr($file, 0, -4));
                if ($nbt) {
                    Factions::_add(new Faction($nbt, $this->plugin->getServer()));
                    if (Factions::_getFactionById($nbt->ID->getValue()) instanceof Faction) {
                        $this->plugin->getLogger()->debug(Text::parse('faction.load.success', $nbt->Name->getValue()));
                    } else {
                        $this->plugin->getLogger()->debug(Text::parse('faction.load.failed', $nbt->Name->getValue()));
                    }
                }
            }
        }
    }
    
    /**
     * @param string $name
     *
     * @return CompoundTag
     */
    public function getSavedFactionData($name) : CompoundTag
    {
        $name = strtolower($name);
        if(file_exists(self::$factionsDataFolder . "$name.dat")){
            try{
                $nbt = new NBT(NBT::BIG_ENDIAN);
                $nbt->readCompressed(file_get_contents(self::$factionsDataFolder . "$name.dat"));

                $nbt = $nbt->getData();
                if(!isset($nbt->Name) or $nbt->Name == "") throw new \Exception("Invalid name");

                return $nbt;
            }catch(\Throwable $e){ //zlib decode error / corrupt data
                rename(self::$factionsDataFolder . "$name.dat", self::$factionsDataFolder . "$name.dat.bak");
                $this->plugin->getLogger()->notice(Text::parse('plugin.data.factionFile.corrupted', $name));
            }
        }else{
            $this->plugin->getLogger()->notice(Text::parse('plugin.data.factionFile.notFound', $name));
        }
        return null;
    }

    /**
     * @param Faction $faction
     * @return bool
     */
    public function deleteFactionData(Faction $faction) : bool {
        @unlink(self::$factionsDataFolder.strtolower($faction->getName()).".dat");
        return file_exists(self::$factionsDataFolder.strtolower($faction->getName()).".dat") === false;
    }

    public function getSavedPlayerData($name) : ARRAY
    {
        // TODO: Implement getSavedPlayerData() method.
    }

    public function savePlayerData(FPlayer $player, $async = false)
    {
        // TODO: Implement savePlayerData() method.
    }

    protected function init()
    {
        // TODO: Implement init() method.
    }
}