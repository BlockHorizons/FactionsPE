<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.25.3
 * Time: 22:25
 */

namespace BlockHorizons\FactionsPE\data\provider;


use BlockHorizons\FactionsPE\data\FactionData;
use BlockHorizons\FactionsPE\data\MemberData;
use BlockHorizons\FactionsPE\entity\Plot;

class SQLite3DataProvider extends DataProvider
{

    public function saveMember(MemberData $member)
    {
        // TODO: Implement saveMember() method.
    }

    public function saveFaction(FactionData $faction)
    {
        // TODO: Implement saveFaction() method.
    }

    /**
     * @param string $name
     * @return MemberData|null
     */
    public function loadMember(string $name)
    {
        // TODO: Implement loadMember() method.
    }

    public function loadFactions()
    {
        // TODO: Implement loadFactions() method.
    }

    /**
     * @param string $id
     * @return FactionData|null
     */
    public function loadFaction(string $id)
    {
        // TODO: Implement loadFaction() method.
    }

    /**
     * @param string
     */
    public function deleteMember(string $identifier)
    {
        // TODO: Implement deleteMember() method.
    }

    /**
     * @param string
     */
    public function deleteFaction(string $identifier)
    {
        // TODO: Implement deleteFaction() method.
    }

    public function savePlots(array $plots)
    {
        // TODO: Implement savePlots() method.
    }

    /**
     * Must set plots using Plots::setPlots()
     */
    public function loadPlots()
    {
        // TODO: Implement loadPlots() method.
    }

    public function loadFlags()
    {
        // TODO: Implement loadFlags() method.
    }

    public function saveFlags(array $flags)
    {
        // TODO: Implement saveFlags() method.
    }

    public function loadPermissions()
    {
        // TODO: Implement loadPermissions() method.
    }

    public function savePermissions(array $permissions)
    {
        // TODO: Implement savePermissions() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function deletePlot(Plot $plot)
    {
        // TODO: Implement deletePlot() method.
    }

    protected function prepare()
    {
        // TODO: Implement prepare() method.
    }
}