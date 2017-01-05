<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\engine;

use factions\flag\Flag;
use factions\entity\Member;
use factions\manager\Plots;
use factions\manager\Members;
use factions\manager\Flags;
use factions\relation\Relation;
use factions\utils\Gameplay;
use factions\utils\Text;

use localizer\Localizer;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class CombatEngine extends Engine {

    /**
     * @priority LOWEST
     */
    public function onPlayerDamage(EntityDamageEvent $event)
    {
        if ($event->isCancelled()) {            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if($event->getEntity() instanceof Player === false) return;
            if (CombatEngine::canCombatDamageHappen($event, true) === false) {
                $event->setCancelled(true);            }
        } else {
            if (CombatEngine::canDamageHappen($event, true) === false) {
                $event->setCancelled(true);            }
        }
    }

    public static function canCombatDamageHappen(EntityDamageByEntityEvent $event, $notify = true) : bool {
		
		$victim = $event->getEntity();
        $attacker = $event->getDamager();
        $mattacker = $attacker instanceof Player ? Members::get($attacker) : null;
        $mdefender = Members::get($victim);
        $defendFaction = $mdefender->getFaction();
        $attackFaction = $mattacker->getFaction(); # ERROR

        if ($mattacker !== null && $mdefender->isOverriding()) return true;
        
        $victimPosFac = Plots::getFactionAt($victim);
        
        if($mattacker->hasFaction() || $mdefender->hasFaction()) {
            if($mattacker->getFaction() === $mdefender->getFaction() && !$victimPosFac->getFlag(Flag::FRIENDLY_FIRE) || Relation::isFriend($relation = $defendFaction->getRelationTo($attackFaction)))
            {
                if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-allies"));
                return false;
            }
        }

        $attackerPosFac = Plots::getFactionAt($attacker);
        
        if (!$victimPosFac->getFlag(Flag::PVP))
        {
            if ($attacker instanceof Player)
            {
                if ($notify) $attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$victimPosFac->getName()]));
                return false;
            }
            return $victimPosFac->getFlag(Flag::MONSTERS);
        }

        if (!$attackerPosFac->getFlag(Flag::PVP))
        {
            if ($notify) $attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$attackerPosFac->getName()]));
            return false;
        }

        // ... are PVP rules completely ignored in this world? ...
        if (in_array($attacker->getLevel()->getName(), Gameplay::get("worlds-pvp-rules-enabled", []), true)) return true;

        if ($defendFaction->isNone()) 
        {
            if ($victimPosFac === $attackFaction && Gameplay::get("enable-pvp-against-factionless-in-attackers-land", true))
            {
                return true;
            } elseif (Gameplay::get("disable-pvp-for-factionless-players", true))
            {
                if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-factionless"));
                return false;
            } elseif ($attackFaction->isNone() && Gameplay::get("enable-pvp-between-factionless-players", true)) {
                return true;
            }
        } elseif ($attackFaction->isNone() && Gameplay::get("enable-pvp-for-factionless-players", true))
        {
            $attacker->sendMessage(Localizer::translatable("cant-hurt-while-factionless"));
            return false;        
        } 

		// You can not hurt neutrals in their own territory.
		if ($mdefender->hasFaction() && $mdefender->isInOwnTerritory() && $relation === Relation::NEUTRAL) 
        {
            if ($notify) {
                $attacker->sendMessage(Localizer::translatable("cant-hurt-in-their-territory", [$mdefender->getDisplayName()]));
                $mdefender->sendMessage(Localizer::translatable("player-tried-to-hurt-you", [$attacker->getDisplayName()]));
            }
            return false;
        }
        return true;
    }

    //////////////////////// EVENTS ////////////////////////
    public static function canDamageHappen(EntityDamageEvent $e, $notify = true) : bool {
        # TODO
        return true;
    }

}