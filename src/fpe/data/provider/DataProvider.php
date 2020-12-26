<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\data\provider;

use Exception;
use fpe\data\FactionData;
use fpe\data\MemberData;
use fpe\entity\Plot;
use fpe\FactionsPE;
use fpe\manager\Flags;
use fpe\manager\Permissions;
use localizer\Localizer;

abstract class DataProvider
{

    /** @var FactionsPE */
    protected $main;

    /**
     * DataProvider constructor
     * @param FactionsPE $main
     * @throws Exception
     */
    public function __construct(FactionsPE $main)
    {
        $this->main = $main;
        $this->prepare();
    }

    protected abstract function prepare();

    /**
     * @param $file
     * @param bool $create
     * @param string $default
     * @return string
     */
    public static function readFile($file, $create = false, $default = ""): string
    {
        $file = str_replace("__DATAFOLDER__", FactionsPE::get()->getDataFolder(), $file);
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
    public static function writeFile($file, $data)
    {
        $file = str_replace("__DATAFOLDER__", FactionsPE::get()->getDataFolder(), $file);
        $f = fopen($file, "w");
        if ($f) {
            fwrite($f, $data);
        }
        fclose($f);
    }

    public static function order(array $factions)
    {
        usort($factions, function ($a, $b) {
            $pa = self::internalPriority($a);
            $pb = self::internalPriority($b);
            if ($pa !== $pb) return $pa <=> $pb;
            return $a <=> $b;
        });
        return $factions;
    }

    public static function internalPriority($name, $ext = ""): int
    {
        switch (strtolower($name)) {
            case "wilderness" . $ext:
                return 1;
            case "warzone" . $ext:
                return 2;
            case "safezone" . $ext:
                return 3;
            default:
                return PHP_INT_MAX;
        }
    }


    // ---------------------------------------------------------------------------
    // ABSTRACT FUNCTIONS
    // ---------------------------------------------------------------------------

    public abstract function saveMember(MemberData $member);

    public abstract function saveFaction(FactionData $faction);

    /**
     * @param string $name
     * @return MemberData|null
     */
    public abstract function loadMember(string $name);

    public abstract function loadFactions();

    /**
     * @param string $id
     * @return FactionData|null
     */
    public abstract function loadFaction(string $id);

    /**
     * @param string
     */
    public abstract function deleteMember(string $identifier);

    /**
     * @param string
     */
    public abstract function deleteFaction(string $identifier);

    public abstract function savePlots(array $plots);

    /**
     * Must set plots using Plots::setPlots()
     */
    public abstract function loadPlots();

    /**
     * @param Plot $plot
     * @return void
     */
    public abstract function deletePlot(Plot $plot);

    /**
     * Call {@link $this->loadFlag()} to load individual flag
     * @return void
     */
    public abstract function loadFlags();

    public abstract function saveFlags(array $flags);

    public function loadFlag(string $id, array $data): bool
    {
        $desc = Localizer::translatable($data["desc"]);
        $descYes = Localizer::translatable($data["descYes"]);
        $descNo = Localizer::translatable($data["descNo"]);
        $flag = Flags::create($id, $data["priority"], $data["name"], $desc, $descYes, $descNo, $data["standard"], $data["editable"], $data["visible"]);
        return Flags::contains($flag);
    }

    /**
     * Call {@link $this->loadPermission()} to load individual permission
     * @return void
     */
    public abstract function loadPermissions();

    public abstract function savePermissions(array $permissions);

    public function loadPermission(string $id, array $data): bool
    {
        $desc = Localizer::translatable("permission." . $id);
        $flag = Permissions::create($data["priority"], $id, $id, $desc, $data["standard"], $data["territory"], $data["editable"], $data["visible"]);
        return Permissions::contains($flag);
    }

    public abstract function close();

    public abstract function getName(): string;

    protected function getMain(): FactionsPE
    {
        return $this->main;
    }

}
