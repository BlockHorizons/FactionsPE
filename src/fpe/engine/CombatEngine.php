<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\engine;

use fpe\flag\Flag;
use fpe\manager\Members;
use fpe\manager\Plots;
use fpe\relation\Relation;
use fpe\utils\Gameplay;
use localizer\Localizer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

class CombatEngine extends Engine
{

    /**
     * @priority LOWEST
     * @param PlayerDeathEvent
     */
    public function onPlayerDeath(PlayerDeathEvent $event) {
        if($event->getPlayer()->getLastDamageCause() instanceof EntityDamageByEntityEvent) {
            $attacker = $event->getPlayer()->getLastDamageCause()->getDamager();
            if($attacker instanceof Player) {
                $fplayer = Members::get($event->getPlayer());
                $fattacker = Members::get($attacker);

                // Inform friendly fire
                if($ff = (Relation::sameFaction($fattacker, $fplayer) || Relation::isAlly($fattacker, $fplayer))) {
                    $ffMessage = Localizer::translatable('friendly-fire', [
                        $fattacker->getDisplayName(), $fplayer->getDisplayName()
                    ]);
                    switch(strtolower(Gameplay::get('broadcast-friendly-fire', 'faction'))) {
                        case 'faction':
                            $fplayer->getFaction()->sendMessage($ffMessage);
                            if($fplayer->getFaction() !== $fattacker->getFaction()) {
                                $fattacker->getFaction()->sendMessage($ffMessage);
                            }
                            break;
                        case 'all':
                            $this->getMain()->getServer()->broadcastMessage($ffMessage);
                            break;
                        default:
                            # Silence
                            break;
                    }
                }

                // Has attacker earned the power points if he killed ally...
                if($ff && !Gameplay::get('allow-ally-kill-bonus', false)) {
                    $fattacker->sendMessage(Localizer::translatable('ally-kill-no-bonus'));
                    return;
                }
                
                // Is powergain enabled in this world?
                if (!in_array($attacker->getLevel()->getFolderName(), Gameplay::get("world-power-gain-enabled", []), true)) {
                    $fattacker->sendMessage(Localizer::translatable("no-powergain-due-to-world"));
                    return;
                }
                
                // Calculate power gain
                $bonus = Gameplay::get('power-per-kill', 10);
                $fattacker->addPower($bonus);
                // Inform
                $fattacker->sendMessage(Localizer::translatable('power-gained-from-kill', [
                    $bonus,
                    'rel-color' => Relation::getColorOfThatToMe($fattacker, $fplayer),
                    $fplayer->getDisplayName()
                ]));                
            }
        }
    }

    /**
     * @priority LOWEST
     * @param EntityDamageEvent $event
     */
    public function onPlayerDamage(EntityDamageEvent $event)
    {
        if ($event->isCancelled()) {
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getEntity() instanceof Player === false) return;
            if (CombatEngine::canCombatDamageHappen($event, true) === false) {
                $event->setCancelled(true);
            }
        } else {
            if (CombatEngine::canDamageHappen($event, true) === false) {
                $event->setCancelled(true);
            }
        }
    }

    public static function canCombatDamageHappen(EntityDamageByEntityEvent $event, $notify = true): bool
    {

        $victim = $event->getEntity();
        $attacker = $event->getDamager();
        $mattacker = $attacker instanceof Player ? Members::get($attacker) : null;
        $mdefender = Members::get($victim);
        $defendFaction = $mdefender->getFaction();
        if ($mattacker == null) {
            return false;
        }        
        $attackFaction = $mattacker->getFaction(); # ERROR

        if ($mattacker !== null && $mdefender->isOverriding() || $mattacker->isOverriding()) return true;

        $victimPosFac = Plots::getFactionAt($victim);
        $relation = $defendFaction->getRelationTo($attackFaction);

        if ($mattacker->hasFaction() || $mdefender->hasFaction()) {
            if ($mattacker->getFaction() === $mdefender->getFaction() && !$victimPosFac->getFlag(Flag::FRIENDLY_FIRE) || Relation::isFriend($relation)) {
                if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-allies"));
                return false;
            }
        }

        $attackerPosFac = Plots::getFactionAt($attacker);

        if (!$victimPosFac->getFlag(Flag::PVP)) {
            if ($attacker instanceof Player) {
                if ($notify) $attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$victimPosFac->getName()]));
                return false;
            }
            return $victimPosFac->getFlag(Flag::MONSTERS);
        }

        if (!$attackerPosFac->getFlag(Flag::PVP)) {
            if ($notify) $attacker->sendMessage(Localizer::translatable("pvp-disabled-in", [$attackerPosFac->getName()]));
            return false;
        }

        // ... are PVP rules completely ignored in this world? ...
        if (in_array($attacker->getLevel()->getName(), Gameplay::get("worlds-pvp-rules-enabled", []), true)) return true;

        if ($defendFaction->isNone()) {
            // Players can attack faction-less players if they are on their land
            if ($victimPosFac === $attackFaction && Gameplay::get("enable-pvp-against-factionless-in-attackers-land", true)) {
                return true;
            // Players are able to attach each other if they don't have factions
            } elseif ($attackFaction->isNone() && Gameplay::get("enable-pvp-between-factionless-players", true)) {
                return true;
            // Can't attack players if they are faction-less
            } elseif (Gameplay::get("can-member-attack-factionless", true)) {
                if ($notify) $attacker->sendMessage(Localizer::translatable("cant-hurt-factionless"));
                return false;
            }
        } elseif ($attackFaction->isNone() && !Gameplay::get("can-hurt-while-factionless", true)) {
            $attacker->sendMessage(Localizer::translatable("cant-hurt-while-factionless"));
            return false;
        }

        // You can not hurt neutrals in their own territory.
        if ($mdefender->hasFaction() && $mdefender->isInOwnTerritory() && $relation === Relation::NEUTRAL) {
            if ($notify) {
                $attacker->sendMessage(Localizer::translatable("cant-hurt-in-their-territory", [$mdefender->getDisplayName()]));
                $mdefender->sendMessage(Localizer::translatable("player-tried-to-hurt-you", [$attacker->getDisplayName()]));
            }
            return false;
        }
        return true;
    }

    //////////////////////// EVENTS ////////////////////////
    public static function canDamageHappen(EntityDamageEvent $e, $notify = true): bool
    {
        # TODO
        return true;
    }

}
