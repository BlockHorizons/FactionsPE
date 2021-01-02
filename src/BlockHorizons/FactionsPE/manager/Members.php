<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\manager;

use BlockHorizons\FactionsPE\entity\IMember;
use BlockHorizons\FactionsPE\entity\Member;
use BlockHorizons\FactionsPE\entity\OfflineMember;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\utils\Text;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\IPlayer;
use pocketmine\Player;

class Members
{

    /**
     * @var IMember[]
     */
    private static $players = [];

    /**
     * Get instanceof IMember by player identifier
     * @param $player string|IPlayer|IMember|CommandSender
     * @param bool $create
     * @return IMember|Member|OfflineMember|null
     */
    public static function get($player, bool $create = true)
    {
        if (!$player) {
            throw new InvalidArgumentException("argument 1 passed to " . __CLASS__ . "::" . __METHOD__ . " must be IMember, IPlayer, CommandSender or string, " . Text::toString($player, false) . " given");
        }
        if ($player instanceof ConsoleCommandSender) {
            return self::get("console");
        }
        if ($player instanceof IMember) {
            if ($player instanceof OfflineMember) {
                $player = $player->getName();
            } else {
                $player = $player->getPlayer();
            }
        }
        if ($player instanceof IPlayer) {
            // Handling player object
            if (($ret = self::getByName($player->getName())) !== null) {
                // if stored instance is offline - detach from storage
                if (($ret instanceof Member) === false) {
                    self::detach($ret);
                }
                return $ret;
            }
            return self::create($player);
        } else {
            // Handling name. It can be part of online player name, or exact offline player name
            if (($po = FactionsPE::get()->getServer()->getPlayer($player))) {
                return Members::get($po);
            }
            if (($ret = self::getByName($player)) !== null) {
                return $ret;
            }
            if ($create)
                return self::createOffline($player);
        }
        return null;
    }

    /**
     * This function won't create new IMember instance it will just check
     * is there offline/online player stored in self::$players
     * with the same name
     *
     * @return IMember|null
     * @param $name
     */
    public static function getByName($name)
    {
        foreach (self::$players as $player) {
            if (strtolower($player->getName()) === strtolower($name)) return $player;
        }
        return null;
    }

    /**
     * Remove a member from storage
     * @param IMember $player
     */
    public static function detach(IMember $player)
    {
        if (self::contains($player)) {
            unset(self::$players[$player->getName()]);
            if (!$player->isDefault()) {
                $player->save();
            }
        }
    }

    public static function contains(IMember $player): bool
    {
        return isset(self::$players[$player->getName()]);
    }

    /**
     * Create a Member instance for Player if he doesn't have one already
     * @param Player $player
     * @param bool $attach should this instance be saved
     */
    public static function create(Player $player, bool $attach = true)
    {
        if (($ret = self::getByName($player->getName())) !== NULL) return $ret;
        $m = new Member($player);
        if($attach) {
            self::attach($m);
        }
        return $m;
    }

    /**
     * Returns a OfflinePlayer instance for player with $name
     * @param string $name
     * @param bool $attach should this instance be saved
     * @return OfflineMember
     */
    public static function createOffline(string $name, bool $attach = true): OfflineMember
    {
        /** @var OfflineMember $ret */
        if (($ret = self::getByName($name)) !== NULL) return $ret;
        $m = new OfflineMember($name);
        if($attach) {
            self::attach($m);
        }
        return $m;
    }

    public static function getFactionless(): array
    {
        $r = [];
        foreach (self::$players as $player) {
            if (!$player->isOnline()) continue;
            if ($player->hasFaction()) continue;
            $r[] = $player;
        }
        return $r;
    }

    public static function attach(IMember $player)
    {
        if (!self::contains($player)) self::$players[$player->getName()] = $player;
    }

    /**
     * @return Member[]
     */
    public static function getAllOnline(): array
    {
        $ret = [];
        foreach (self::$players as $Member) {
            if ($Member->isOnline() && $Member instanceof Member) $ret[] = $Member;
        }
        return $ret;
    }

    /**
     * Makes sure that every member data is saved
     */
    public static function saveAll()
    {
        foreach (self::getAll() as $player) {
            $player->save();
        }
    }

    /**
     * @return IMember[]
     */
    public static function getAll()
    {
        return self::$players;
    }

    public function __debugInfo()
    {
        return [
            "objects" => count(self::getAll()),
            "players" => array_map(function ($item) {
                return ($item->isOnline() ? "(Online)" : "(Offline)")." ".$item->getName();
            }, self::getAll())
        ];
    }

}
