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
namespace factions\engine;

use factions\entity\FPlayer;
use factions\event\player\PlayerPowerChangeEvent;
use factions\event\player\PlayerTraceEvent;
use factions\objs\Factions;
use factions\objs\Plots;
use factions\utils\Collections;
use factions\utils\Constants;
use factions\utils\HUD;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use evalcore\engine\Engine;

/**
 * This engine will update player info, such as faction, nametag, money etc.
 *
 * Class FactionEngine
 * @package factions\engine
 */
class PlayerEngine extends Engine
{

    //////////////////////// EVENTS ////////////////////////

    /**
     * @param PlayerPreLoginEvent $e
     * @priority MONITOR
     */
    public function onPlayerPreLogin(PlayerPreLoginEvent $e)
    {
        FPlayer::get($e->getPlayer());
    }

    /**
     * @param PlayerJoinEvent $e
     * @priority MONITOR
     */
    public function onPlayerJoin(PlayerJoinEvent $e) {}

    /**
     * @param PlayerRespawnEvent $e
     * @priority HIGHEST
     */
    public function onPlayerRespawn(PlayerRespawnEvent $e)
    {
        $fplayer = FPlayer::get($e->getPlayer());
        $fplayer->factionHereId = Plots::get()->getFactionAt($fplayer->getPlayer())->getId();
    }

    /**
     * @param PlayerDeathEvent $e
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerDeath(PlayerDeathEvent $e){
        $fplayer = FPlayer::get($e->getPlayer());
        $minus = $fplayer->getPowerPerDeath();
        $event = new PlayerPowerChangeEvent($fplayer, $fplayer->getPower() - $minus, PlayerPowerChangeEvent::DEATH);
        $this->getCore()->getServer()->getPluginManager()->callEvent($event);
        if($event->isCancelled()) return;
        $fplayer->setPower($event->getNewPower());
    }

    /**
     * @param PlayerQuitEvent $e
     * @priority HIGHEST
     */
    public function onPlayerQuit(PlayerQuitEvent $e)
    {
        $fplayer = FPlayer::get($e->getPlayer());
        HUD::get()->removeViewer($fplayer);
        $fplayer->save();
        FPlayer::detach($fplayer);
    }

    /**
     * @param PlayerMoveEvent $event
     * @priority LOWEST
     */
    public function playerMoveEvent(PlayerMoveEvent $event)
    {
        $from = $event->getFrom();
        $to = $event->getTo();
        if (($from->x >> 4 === $to->x >> 4) && ($from->z >> 4 === $to->z >> 4)) return;
        $fplayer = FPlayer::get($event->getPlayer());
        $fHere = Plots::get()->getFactionAt($fplayer->getPlayer());
        if ($fplayer->factionHereId !== $fHere->getId()) {
            $event = new PlayerTraceEvent($fplayer, $fplayer->factionHereId, $fHere->getId());
            $this->getCore()->getServer()->getPluginManager()->callEvent($event);
            $fplayer->factionHereId = $fHere->getId();
        }
    }

    /**
     * @param PlayerTraceEvent $event
     * @priority LOWEST
     * @ignoreCancelled false
     */
    public function playerTraceEvent(PlayerTraceEvent $event)
    {
        /** @var FPlayer $player */
        $player = $event->getPlayer();
        $faction = Factions::getById($event->getToFactionId());
        $player->getPlayer()->sendMessage(
            "~ " . $faction->getColorTo($player) . $faction->getName() . ($faction->hasDescription() ? " - " . $faction->getDescription() : ""));
        if ($player->isMapAutoUpdating()) {
            $map = Collections::getMap($player->getPlayer(), Constants::MAP_WIDTH - 12, Constants::MAP_HEIGHT, $player->getPlayer()->getYaw());
            foreach ($map as $line) {
                $player->sendMessage($line);
            }
        }

        if($player->isAutoClaiming()) {
            $f = Factions::getById($player->getAutoClaimFaction());
            $this->getCore()->getServer()->dispatchCommand($player->getPlayer(), "/f claim one ".$f->getName());
        }
    }

}