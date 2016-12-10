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


use factions\entity\Flag;
use factions\entity\FPlayer;
use factions\objs\Plots;
use factions\objs\Rel;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class CombatEngine extends Engine
{

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

    public static function canCombatDamageHappen(EntityDamageByEntityEvent $event, $notify = true) : bool
    {
		// If the defender is a player ...
		$victim = $event->getEntity();
        
		// ... and the attacker is someone else ...
		$attacker = $event->getDamager();

		// ... gather defender PS and faction information ...
		$defenderPosFaction = Plots::get()->getFactionAt($victim);

		// ... fast evaluate if the attacker is overriding ...
		$fattacker = $attacker instanceof Player ? FPlayer::get($attacker) : NULL;
        $fdefender = FPlayer::get($victim);

		if ($fattacker != null && $fdefender->isOverriding()) return true;

		// ... PVP flag may cause a damage block ...
		if ($defenderPosFaction->getFlag(Flag::PVP) == false)
        {
            if ($attacker instanceof Player)
            {
                $ret = false;
				if (!$ret && $notify)
                {
                    $attacker = FPlayer::get($attacker);
					$attacker->sendMessage(Text::parse("<i>PVP is disabled in %var0.", $defenderPosFaction->getName()));
				}
				return $ret;
			}
            return $defenderPosFaction->getFlag(Flag::MONSTERS);
        }

		$fattacker = FPlayer::get($fattacker);

		// ... does this player bypass all protection? ...
		//if (Settings::get()->playersWhoBypassAllProtection.contains(attacker.getName())) return true;

		// ... gather attacker PS and faction information ...
		$attackerPos = $attacker->getPosition();
		$attackerPosFaction = Plots::get()->getFactionAt($attackerPos);

		// ... PVP flag may cause a damage block ...
		// (just checking the defender as above isn't enough. What about the attacker? It could be in a no-pvp area)
		// NOTE: This check is probably not that important but we could keep it anyways.
		if ($attackerPosFaction->getFlag(Flag::PVP) == false)
        {
            $ret = false;
            if (!$ret && $notify) $attacker->sendMessage(Text::parse("<i>PVP is disabled in %var0.", $attackerPosFaction->getName()));
            return $ret;
        }

		// ... are PVP rules completely ignored in this world? ...
		if (in_array($attackerPos->getLevel()->getName(), Settings::get("worldsPvpRulesEnabled", []), true)) return true;

		$defendFaction = $fdefender->getFaction();
		$attackFaction = $fattacker->getFaction();

		if ($attackFaction->isNone() && Settings::get("disablePVPForFactionlessPlayers", true))
        {
            $ret = false;
            if (!$ret && $notify) $attacker->sendMessage(Text::parse("<i>You can't hurt other players until you join a faction."));
            return $ret;
        }
        elseif ($defendFaction->isNone())
        {
            if ($defenderPosFaction == $attackFaction && Settings::get("enablePVPAgainstFactionlessInAttackersLand", true))
            {
                // Allow PVP vs. Factionless in attacker's faction territory
                return true;
            }
            elseif (Settings::get("disablePVPForFactionlessPlayers", true))
            {
                $ret = false;
                if (!$ret && $notify) $attacker->sendMessage(Text::parse("<i>You can't hurt players who are not currently in a faction."));
                return $ret;
            }
            elseif ($attackFaction->isNone() && Settings::get("enablePVPBetweenFactionlessPlayers", true))
            {
                // Allow factionless vs factionless
                return true;
            }
        }

		$relation = $defendFaction->getRelationTo($attackFaction);

		// Check the relation
		if (Rel::isFriend($relation) && $defenderPosFaction->getFlag(Flag::FRIENDLY_FIRE) === false)
        {
            $ret = false;
            if (!$ret && $notify) $attacker->sendMessage(Text::parse("<i>You can't hurt allies<i>."));
            return $ret;
        }

		// You can not hurt neutrals in their own territory.
		$ownTerritory = $fdefender->isInOwnTerritory();

		if ($fdefender->hasFaction() && $ownTerritory && $relation === Rel::NEUTRAL)
        {
            $ret = false;
            if (!$ret && $notify)
            {
                $attacker->sendMessage(Text::parse("<i>You can't hurt %var0<i> in their own territory unless you declare them as an enemy.", $fdefender->getDisplayName()));
                $fdefender->sendMessage("%var0<i> tried to hurt you.", $attacker->getDisplayName());
            }
            return $ret;
        }


        return true;
    }


    //////////////////////// EVENTS ////////////////////////

    public static function canDamageHappen(EntityDamageEvent $e, $notify = true) : bool {
        # TODO
        return true;
    }

}