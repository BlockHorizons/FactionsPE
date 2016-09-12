<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/23/16
 * Time: 8:28 PM
 */

namespace factions\data;


use factions\faction\Faction;
use factions\FactionsPE;
use pocketmine\nbt\tag\CompoundTag;

class DummyDataProvider extends DataProvider
{

    /**
     * DummyDataProvider constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin);
    }

    /*** ABSTRACT FUNCTIONS ***/
    public function loadSavedFactions()
    {
        return true;
    }

    public function saveFactionData($name, CompoundTag $tag, $async = false)
    {
        return true;
    }

    public function getSavedFactionData($name) : CompoundTag
    {
    }

    public function deleteFactionData(Faction $faction)
    {
        return false;
    }
}