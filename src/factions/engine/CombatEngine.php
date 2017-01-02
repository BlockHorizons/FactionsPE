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
        if ($event->isCancelled()) {
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if($event->getEntity() instanceof Player === false) return;
            if (CombatEngine::canCombatDamageHappen($event, true) === false) {
                $event->setCancelled(true);
            }
        } else {
            if (CombatEngine::canDamageHappen($event, true) === false) {
                $event->setCancelled(true);
            }
        }
    }

    public static function canCombatDamageHappen(EntityDamageByEntityEvent $event, $notify = true) : bool {
		
        // If the defender is a player ...
		$victim = $event->getEntity();
		// ... and the attacker is someone else ...
		$attacker = $event->getDamager();
		// ... gather defender PS and faction information ...
		$defenderPosFaction = Plots::get()->getFactionAt($victim);
		// ... fast evaluate if the attacker is overriding ...
		$mattacker = $attacker instanceof Player ? Members::get($attacker) : null;
        $mdefender = Members::get($victim);
		
        if ($mattacker != null && $mdefender->isOverriding()) return true;
		
        // ... PVP flag may cause a damage block ...
		if ($defenderPosFaction->getFlag(Flag::PVP) == false) {
            if ($attacker instanceof Player) {
				if ($notify) {
                    $attacker = Members::get($attacker);
					$attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$defenderPosFaction->getName()]));
				}
				return false;
			}
            return $defenderPosFaction->getFlag(Flag::MONSTERS);
        }
		$mattacker = Members::get($mattacker);
		// ... does this player bypass all protection? ...
		// ... gather attacker PS and faction information ...
		$attackerPos = $attacker->getPosition();
		$attackerPosFaction = Plots::get()->getFactionAt($attackerPos);
		// ... PVP flag may cause a damage block ...
		// (just checking the defender as above isn't enough. What about the attacker? It could be in a no-pvp area)
		// NOTE: This check is probably not that important but we could keep it anyways.
		if ($attackerPosFaction->getFlag(Flag::PVP) == false) {
            $ret = false;
            if (!$ret && $notify){ 
                $attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$attackerPosFaction->getName()]));
            }
            return $ret;
        }
		// ... are PVP rules completely ignored in this world? ...
		if (in_array($attackerPos->getLevel()->getName(), Gameplay::get("worlds-pvp-rules-enabled", []), true)) return true;
		$defendFaction = $mdefender->getFaction();
		$attackFaction = $mattacker->getFaction();
		if ($attackFaction->isNone() && Gameplay::get("disable-pvp-for-factionless-players", true)){
            $attacker->sendMessage(Localizer::translatable("cant-hurt-while-factionless"));
            return false;
        } elseif ($defendFaction->isNone()) {
            if ($defenderPosFaction == $attackFaction && Gameplay::get("enable-pvp-against-factionless-in-attackers-land", true)) {
                // Allow PVP vs. Factionless in attacker's faction territory
                return true;
            } elseif (Gameplay::get("disable-pvp-for-factionless-players", true)) {
                if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-factionless"));
                return false;
            } elseif ($attackFaction->isNone() && Gameplay::get("enable-pvp-between-factionless-players", true)) {
                // Allow factionless vs factionless
                return true;
            }
        }
		$relation = $defendFaction->getRelationTo($attackFaction);
		// Check the relation
		if (Relation::isFriend($relation) && !$defenderPosFaction->getFlag(Flag::FRIENDLY_FIRE)) {
            if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-allies"));
            return false;
        }
		// You can not hurt neutrals in their own territory.
		$ownTerritory = $mdefender->isInOwnTerritory();
		if ($mdefender->hasFaction() && $ownTerritory && $relation === Relation::NEUTRAL) {
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