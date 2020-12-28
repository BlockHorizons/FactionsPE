<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\manager;

use fpe\entity\Faction;
use fpe\entity\IMember;
use fpe\entity\Plot;
use fpe\event\LandChangeEvent;
use fpe\FactionsPE;
use localizer\Localizer;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Plots
{
    /**
     * 2 ^ CHUNK_SIZE
     * @var int
     */
    public static int $CHUNK_SIZE = 4;

    /** @var string[] hash => faction */
    private static $plots = [];

    public static function setPlots(array $plots)
    {
        self::$plots = $plots;
    }

    /**
     * Get owner Faction object for plot in this position
     *
     * @param Plot|Position $plot
     * @return Faction owner of plot
     */
    public static function getFactionAt(Position $plot): Faction
    {
        if (!$plot instanceof Plot) $plot = new Plot($plot);
        $faction = Factions::getById(self::getOwnerId($plot));
        if ($faction === null) {
            # Error, this can only happen if faction is deleted
            return Factions::getById(Faction::NONE);
        }
        return $faction;
    }

    /**
     * Get owner Faction id for plot in this position
     * @param Plot $plot
     * @return string
     */
    public static function getOwnerId(Position $plot): string
    {
        return self::$plots[self::hash($plot)] ?? Faction::NONE;
    }

    /**
     * Will stringify plot position to identify it
     *
     * @param Plot $pos
     * @return string
     */
    public static function hash(Position $pos): string 
    {
        if($pos->getLevelNonNull() === null) {
            throw new \InvalidArgumentException("given position($pos) has no valid level attached. Perhaps level not loaded?");
        }
        return $pos->x . ":" . $pos->z . ":" . $pos->level->getFolderName();
    }


    /**
     * Get how many plots have this Faction claimed
     * @param Faction $faction
     * @return int
     */
    public static function getCount(Faction $faction): int
    {
        return count(self::getFactionPlots($faction));
    }

    /**
     * Get all plots claimed by this Faction
     * @param Faction $f
     * @return array|Plot[]
     */
    public static function getFactionPlots(Faction $f): array
    {
        $r = [];
        foreach (self::$plots as $plot => $faction) {
            if ($faction === $f->getId()) $r[] = self::fromHash($plot);
        }
        return $r;
    }


    /**
     * Turn hash string from {@link Plots::hash} back to Plot object
     * @param string (x:z:level)
     * @return Plot|null
     */
    public static function fromHash($hash)
    {
        $d = explode(":", $hash);
        if (count($d) < 3) return null;
        if (!($level = FactionsPE::get()->getServer()->getLevelByName($d[2])) instanceof Level) {
            return null;
        }
        $x = (int)$d[0];
        $z = (int)$d[1];
        return new Plot($x, $z, $level);
    }

    /**
     * Try claim more than one chunk
     * @param Faction $newFaction
     * @param IMember $player
     * @param Plot[] $plots
     */
    public static function tryClaim(Faction $newFaction, IMember $player, array $plots)
    {
        if ($newFaction->isNone()) {
            foreach ($plots as $plot) {
                $plot->unclaim($player, false);
            }
        } else {
            foreach ($plots as $plot) {
                $plot->claim($newFaction, $player);
            }
        }
    }

    /**
     * Set owner Faction for Plot
     * @param Faction $faction new owner
     * @param Plot $plot
     * @param IMember $player who's claiming? if null, it's the Faction leader
     * @param bool $silent = false
     * @return bool
     * @internal param Plot $lot
     */
    public static function claim(Faction $faction, Plot $plot, IMember $player = null, $silent = false): bool
    {
        if (!$player) $player = $faction->getLeader();
        $oldFaction = $plot->getOwnerFaction();
        if ($oldFaction !== $faction) {
            if (!$silent) {
                FactionsPE::get()->getServer()->getPluginManager()->callEvent($e = new LandChangeEvent(LandChangeEvent::CLAIM, $plot, $faction, $player));
                if ($e->isCancelled()) return false;
            }
        } else {
            if (!$silent && $player) $player->sendMessage(Localizer::translatable('plot-already-claimed', [
                "x" => $plot->x,
                "z" => $plot->z,
                "faction" => $oldFaction->getName()
            ]));
            return false;
        }
        self::$plots[$plot->hash()] = $faction->getId();
        if (!$silent && $player) $player->sendMessage(Localizer::translatable('plot-claimed', [
            "x" => $plot->x,
            "z" => $plot->z,
            "faction" => $faction->getName(),
            "old-faction" => $oldFaction->getName()
        ]));
        return true;
    }

    /**
     * Remove owner Faction from plot in given position
     * @param Plot $plot
     * @param IMember|null $player
     * @param bool $silent = false, set to true if you don't want to call any event
     * @return bool
     */
    public static function unclaim(Plot $plot, IMember $player = null, $silent = false): bool
    {
        $faction = $plot->getOwnerFaction();
        if (!$player) $player = $faction->getLeader();
        if (($id = $plot->getOwnerId()) !== Faction::NONE) {
            if (($faction = Factions::getById($id)) instanceof Faction) {
                if (!$silent) {
                    FactionsPE::get()->getServer()->getPluginManager()->callEvent($e = new LandChangeEvent(LandChangeEvent::UNCLAIM, $plot, $faction, $player));
                    if ($e->isCancelled()) return false;
                    unset(self::$plots[$plot->hash()]);
                    FactionsPE::get()->getDataProvider()->deletePlot($plot);
                    if ($player) {
                        $player->sendMessage(Localizer::translatable("plot-unclaimed", [
                            "x" => $plot->x,
                            "z" => $plot->z,
                            "member" => $player->getDisplayName(),
                            "faction" => $faction->getName(),
                            "rel-color" => $player->getColorTo($faction)
                        ]));
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Get all Faction plots in specific world
     * @param Faction $faction
     * @param Level $world
     * @return Plot[]
     */
    public static function getFactionPlotsInLevel(Faction $faction, Level $world): array
    {
        $plots = self::getFactionPlots($faction);
        $r = [];
        foreach ($plots as $plot) {
            if ($plot->getLevel() === $world) $r[] = $plot;
        }
        return $r;
    }

    /**
     * Returns true if plot is next to one of $faction plot
     * @param Plot $plot
     * @param Faction $faction
     * @return BOOL
     */
    public static function isConnectedPlot(Plot $plot, Faction $faction): bool
    {
        $level = $plot->getLevel();
        $nearby = new Plot($plot->x + 1, $plot->z, $level);
        if ($faction === $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x - 1, $plot->z, $level);
        if ($faction === $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x, $plot->z + 1, $level);
        if ($faction === $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x, $plot->z - 1, $level);
        if ($faction === $nearby->getOwnerFaction()) return true;
        return false;
    }

    /**
     * This checks if plot is on border of large faction territory like
     * - - - - - - - - - - - - - - - -
     * - - - - - - X - - - - - - - - -
     * - - - - - X X X - - - - - - - -
     * - - - - - X O X - - - - - - - -
     * - - - - - X X X - - - - - - - -
     * - - - - - - - - - - - - - - - -
     * X - Border plots, O - Not a border plot.
     * @param Plot $plot
     * @return bool
     */
    public static function isBorderPlot(Plot $plot): bool
    {
        $faction = $plot->getOwnerFaction();
        $level = $plot->getLevel();
        $nearby = new Plot($plot->x + 1, $plot->z, $level);
        if ($faction !== $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x - 1, $plot->z, $level);
        if ($faction !== $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x, $plot->z + 1, $level);
        if ($faction !== $nearby->getOwnerFaction()) return true;
        $nearby = new Plot($plot->x, $plot->z - 1, $level);
        if ($faction !== $nearby->getOwnerFaction()) return true;
        return false;
    }

    public static function saveAll()
    {
        if($p = FactionsPE::get()->getDataProvider()) {
            $p->savePlots(self::$plots);
        }
    }

    public static function getAll(): array
    {
        return self::$plots;
    }

}
