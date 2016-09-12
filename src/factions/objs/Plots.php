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

namespace factions\objs;

use factions\data\DataProvider;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\entity\Plot;
use factions\event\LandChangeEvent;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\utils\Text;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class Plots {

    /** @var Plots $instance */
    private static $instance;

    private static $plots = [];
    private static $file = "";

    /**
     * Get instance of this class
     * @return Plots
     */
    public static function get() : Plots
    {
        return self::$instance;
    }

    /**
     * Destroy this class
     */
    public static function close()
    {
        self::get()->save();
        self::$instance = null;
    }

    /**
     * Save all plots
     */
    public function save()
    {
        DataProvider::writeFile(self::$file, json_encode(self::$plots));
    }

    public function __construct(FactionsPE $plugin)
    {
        if (self::$instance instanceof Plots) return; // #Singleton
        self::$instance = $this;
        self::$file = $plugin->getDataFolder() . "plots.json";
        $data = DataProvider::readFile(self::$file, true, "{}");
        if (($r = json_decode($data, true)) !== null) {
            self::$plots = $r;
        } else {
            self::$plots = [];
        }
    }

    // --------------------------------------- //
    // FUNCTIONS
    // --------------------------------------- //

    /**
     * Get owner Faction object for plot in this position
     *
     * @param Position $pos
     * @param bool $chunk = false, Set to true if components in Position is chunk coordinates.
     * @return Faction owner of plot
     */
    public function getFactionAt(Position $pos, $chunk = false) : Faction
    {
        $faction = Factions::getById(self::get()->getOwnerId($pos, $chunk));
        if ($faction === NULL) return Factions::getById(FactionsPE::FACTION_ID_NONE);
        return $faction;
    }

    /**
     * Get owner Faction id for plot in this position
     * @param Position $pos
     * @param bool $chunk = false, Set to true if components in Position is chunk coordinates.
     * @return string
     */
    public function getOwnerId(Position $pos, $chunk = false) : string
    {
        $h = self::hash($pos, $chunk);
        foreach (self::$plots as $faction => $plots) {
            if (in_array($h, $plots, true)) return $faction;
        }
        return "";
    }

    /**
     * Will stringify plot position to identify it
     *
     * @param Position $pos
     * @param bool $chunk = false, Set to true if components in Position is chunk coordinates.
     * @return string
     */
    public static function hash(Position $pos, $chunk = false) : string
    {
        if (!$chunk) return ($pos->x >> 4) . ":" . ($pos->z >> 4) . ":" . $pos->level->getName();
        else return $pos->x . ":" . $pos->z . ":" . $pos->level->getName();
    }

    /**
     * Create a field in array where to save faction plots
     * @param Faction $faction
     */
    public function registerFaction(Faction $faction)
    {
        if (!isset(self::$plots[$faction->getId()])) self::$plots[$faction->getId()] = [];
    }

    /**
     * Remove all Faction field from array, will unclaim all claimed plots by this Faction
     * @param Faction $faction
     */
    public function unregisterFaction(Faction $faction)
    {
        unset(self::$plots[$faction->getId()]);
    }

    /**
     * Remove owner Faction from plot in given position
     * @param Position $pos
     * @param bool $silent = false, set to true if you don't want to call any event
     * @param bool $chunk = false, Set to true if components in Position is chunk coordinates.
     * @return bool
     */
    public function unclaim(Position $pos, $silent = false, $chunk = false)
    {
        if (($id = $this->getOwnerId($pos, $chunk)) !== "") {
            if (($faction = Factions::getById($id)) instanceof Faction) {
                if (!$silent) {
                    if (($player = Server::getInstance()->getPlayer($faction->getLeader()->getName())) instanceof Player) {
                        $player = FPlayer::get($player);
                    } else {
                        $player = null;
                    }
                    Server::getInstance()->getPluginManager()->callEvent($e = new LandChangeEvent($faction, $player, new Plot($pos), LandChangeEvent::UNCLAIM));
                    if ($e->isCancelled()) return false;
                    unset(self::$plots[$faction->getId()][array_search(self::hash($pos), self::$plots[$faction->getId()], true)]);
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
     * Get how many plots have this Faction claimed
     * @param Faction $faction
     * @return int
     */
    public function getCount(Faction $faction) : int {
        return count($this->getFactionPlots($faction));
    }

    /**
     * Get all plots claimed by this Faction
     * @param Faction $faction
     * @return array
     */
    public function getFactionPlots(Faction $faction) : array
    {
        $r = [];
        foreach (self::$plots[$faction->getId()] as $plot) {
            $r[] = self::fromHash($plot);
        }
        return $r;
    }

    /**
     * Turn hash string from {@link Plots::hash} back to Plot object
     * @param string (x:z:level)
     * @return Plot
     */
    public static function fromHash($hash) : Plot
    {
        $d = explode(":", $hash);
        if (count($d) < 3) return false;
        if (!($level = Server::getInstance()->getLevelByName($d[2])) instanceof Level) {
            return false;
        }
        $x = (int)$d[0];
        $z = (int)$d[1];
        return new Plot($x << 4, $z << 4, $level);
    }

    /**
     * Try claim more than one chunk
     * @param Faction $newFaction
     * @param IFPlayer $player
     * @param array $chunks
     */
    public function tryClaim(Faction $newFaction, IFPlayer $player, array $chunks)
    {
        foreach ($chunks as $chunk) {
            $chunk->setComponents($chunk->x << 4, 0, $chunk->z << 4);
            $this->claim($newFaction, $player, $chunk, false);
        }
    }

    /**
     * Set owner Faction for Plot
     * @param Faction $faction owner
     * @param IFPlayer $player who's claiming? if null, it's the Faction leader
     * @param Plot $pos
     * @param bool $silent = false
     * @return bool
     */
    public function claim(Faction $faction, IFPlayer $player = null, Plot $pos, $silent = false) : bool
    {
        if(!$player) $player = $faction->getLeader();
        $oldFaction = $this->getFactionAt($pos);
        if (true) { # TODO Idk what should I do here but I definitely should do something...
            if (!$silent) {
                Server::getInstance()->getPluginManager()->callEvent($e = new LandChangeEvent($faction, $player, new Plot($pos), LandChangeEvent::CLAIM));
                if ($e->isCancelled()) return false;
            }
        } else {
            return false;
        }
        self::$plots[$faction->getId()][] = self::hash($pos);
        if (!$silent) $player->sendMessage(Text::parse('plot.claimed', (($pos->x >> 4) . ":" . ($pos->z >> 4)), $oldFaction->getName()));
        return true;
    }

    /**
     * Get all Faction plots in specific world
     * @param Faction $faction
     * @param Level $world
     * @return array
     */
    public function getFactionPlotsInWorld(Faction $faction, Level $world) : ARRAY
    {
        $plots = $this->getFactionPlots($faction);
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
    public function isConnectedPlot(Plot $plot, Faction $faction) : bool
    {
        $nearby = null;

        $nearby = new Plot($plot->x + 1, $plot->z, $plot);
        if ($faction === $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x - 1, $plot->z, $plot);
        if ($faction === $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x, $plot->z + 1, $plot);
        if ($faction === $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x, $plot->z - 1, $plot);
        if ($faction === $nearby->getOwnerFaction()) return true;

        return false;
    }

    /**
     * @param Plot $plot
     * @return bool
     */
    public function isBorderPlot(Plot $plot) : bool
    {
        $faction = $plot->getOwnerFaction();

        $nearby = new Plot($plot->x + 1, $plot->z, $plot);
        if ($faction !== $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x - 1, $plot->z, $plot);
        if ($faction !== $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x, $plot->z + 1, $plot);
        if ($faction !== $nearby->getOwnerFaction()) return true;

        $nearby = new Plot($plot->x, $plot->z - 1, $plot);
        if ($faction !== $nearby->getOwnerFaction()) return true;

        return false;
    }

}