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
use factions\entity\Perm;
use factions\entity\Plot;
use factions\event\LandChangeEvent;
use factions\event\player\PlayerPowerChangeEvent;
use factions\FactionsPE;
use factions\objs\Plots;
use factions\objs\Rel;
use factions\utils\RelationUtil;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\entity\Creeper;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;

class MainEngine extends Engine
{

    // -------------------------------------------- //
    // CONSTANTS
    // -------------------------------------------- //

    const SPAWN_REASON_NATURAL = 0x0001;
    const SPAWN_REASON_JOCKEY = 0x0002;
    const SPAWN_REASON_CHUNK_GEN = 0x0003;
    const SPAWN_REASON_OCELOT_BABY = 0x0004;
    const SPAWN_REASON_NETHER_PORTAL = 0x0005;
    const SPAWN_REASON_MOUNT = 0x0006;


    // -------------------------------------------- //
    // FACTION SHOW
    // -------------------------------------------- //

    # TODO

    // -------------------------------------------- //
    // UPDATE LAST ACTIVITY
    // -------------------------------------------- //

    public static function updateLastActivity(Player $sender)
    {
        //$player = FPlayer::get($sender);
        //$player->setLastActivityMillis();
    }

    public static function showMotdOnJoin(PlayerJoinEvent $event)
    {
        // THis can be turned on/off by settings
        if (!Settings::get("Settings.showMotdOnJoin", true)) return;

        // Gather info ...
        $player = $event->getPlayer();
        $fplayer = FPlayer::get($player);
        $faction = $fplayer->getFaction();

        // ... if there is a motd ...
        if (!$faction->hasMotd()) return;

        // ... then prepare the messages ...
        $messages = $faction->getMotdMessages();

        // ... and send to the player.

        foreach ($messages as $message) {
            $player->sendMessage($message);
        }

        # TODO
        /*
        if (MConf.get().motdDelayTicks < 0)
        {
            MixinMessage.get().messageOne(player, messages);
        }
        else
        {
            Bukkit.getScheduler().scheduleSyncDelayedTask(Factions.get(), new Runnable()
            {
                @Override
                public function run()
        {
        MixinMessage.get().messageOne(player, messages);
                }
            }, MConf.get().motdDelayTicks);
        }
        */
    }

    // Can't be cancelled

    /**
     * @param PlayerJoinEvent $event
     * @priority LOWEST
     */
    public function updateLastActivityOnJoin(PlayerJoinEvent $event)
    {
        // During the join event itself we want to be able to reach the old data.
        // That is also the way the underlying fallback Mixin system does it and we do it that way for the sake of symmetry. 
        // For that reason we wait till the next tick with updating the value.
        self::updateLastActivitySoon($event->getPlayer());
    }

    // -------------------------------------------- //
    // MOTD
    // -------------------------------------------- //

    public static function updateLastActivitySoon(Player $sender)
    {
        //FactionsPE::get()->getServer()->getScheduler()->scheduleDelayedTask(, 20);
    }

    // -------------------------------------------- //
    // CHUNK CHANGE: DETECT
    // -------------------------------------------- //

    /**
     * @param LandChangeEvent $event
     * @priority LOW
     * @ignoreCancelled true
     */
    public function onChunksChange(LandChangeEvent $event)
    {
        // For security reasons we block the chunk change on any error since an error might block security checks from happening.
        try {
            $this->onChunksChangeInner($event);
        } catch (\Exception $throwable) {
            $event->setCancelled(true);
            echo $throwable->getTraceAsString();
        }
    }

    public function onChunksChangeInner($event)
    {
        if(!$event instanceof LandChangeEvent) throw new \InvalidArgumentException("");
        // Args
        $player = $event->getPlayer();
        $newFaction = $event->getFaction();
        $chunk = $event->getChunk();
        $currentFaction = Plots::get()->getFactionAt($chunk);

        // Override Mode? Sure!
        if ($player->isOverriding()) return;

        // CALC: Is there at least one normal faction among the current ones?
        $currentFactionIsNormal = false;
        if ($currentFaction->isNormal()) $currentFactionIsNormal = true;

        if($event->getChangeType() === LandChangeEvent::CLAIM) {
            // If the new faction is normal (not wilderness/none), meaning if we are claiming for a faction ...
            if ($newFaction->isNormal()) {
                $world = $chunk->getLevel();
                if (!in_array($world->getName(), Settings::get("Settings.worldsClaimingEnabled", []))) {
                    $worldName = $world->getName();
                    $player->sendMessage(Text::parse("<b>Land claiming is disabled in <h>%var0<b>.", $worldName));
                    $event->setCancelled(true);
                    return;
                }

                // ... ensure we have permission to alter the territory of the new faction ...
                if (!Perm::getPermTerritory()->has($player, $newFaction)) {
                    // NOTE: No need to send a message. We send message from the permission check itself.
                    $event->setCancelled(true);
                    return;
                }

                // ... ensure the new faction has enough players to claim ...
                if (count($newFaction->getPlayers()) < Settings::get("Settings.claimsRequireMinFactionMembers", 0)) {
                    $player->sendMessage(Text::parse("<b>Factions must have at least <h>%s<b> members to claim land.", Settings::get("claimsRequireMinFactionMembers", 0)));
                    $event->setCancelled(true);
                    return;
                }

                $claimedLandCount = $newFaction->getLandCount();
                if (!$newFaction->getFlag(Flag::INFINITY_POWER)) {
                    // ... ensure the claim would not bypass the global max limit ...
                    if (Settings::get("Settings.claimedLandsMax", 99999999) != 0 && $claimedLandCount + 1 > Settings::get("Settings.claimedLandsMax", 999999999)) {
                        $player->sendMessage("<b>Limit reached. You can't claim more land.");
                        $event->setCancelled(true);
                        return;
                    }

                }

                // ... ensure the claim would not bypass the faction power ...
                if ($claimedLandCount + 1 > $newFaction->getPowerRounded()) {
                    $player->sendMessage(Text::parse("<b>You don't have enough power to claim that land."));
                    $event->setCancelled(true);
                    return;
                }

                // ... ensure the claim would not violate distance to neighbors ...
                // HOW: Calculate the factions nearby, excluding the chunks themselves, the faction itself and the wilderness $faction->
                // HOW: The chunks themselves will be handled in the "if (old$faction->isNormal())" section below.
                /*
                $nearbyChunks = Plots::get()->getNearbyPlots($chunk, Settings::get("Settings.claimMinimumChunksDistanceToOthers", 1));
                nearbyChunks.removeAll(chunks);
                Set<Faction> nearbyFactions = BoardColl.getDistinctFactions(nearbyChunks);
                nearbyFactions.remove(FactionColl.get().getNone());
                nearbyFactions.remove(newFaction);
                // HOW: Next we check if the new faction has permission to claim nearby the nearby factions.
                MPerm claimnear = MPerm.getPermClaimnear();
                for (Faction nearbyFaction : nearbyFactions)
                {
                    if (claimnear.has(newFaction, nearbyFaction)) continue;
                    mplayer.message(claimnear.createDeniedMessage(mplayer, nearbyFaction));
                    $event->setCancelled(true);
                    return;
                }
                */

                // ... ensure claims are properly connected ...
                if
                (
                    // If claims must be connected ...
                    Settings::get("Settings.claimsMustBeConnected", true)
                    // ... and this faction already has claimed something on this map (meaning it's not their first claim) ...
                    &&
                    $newFaction->getLandCountInWorld($world) > 0
                    // ... and none of the chunks are connected to an already claimed chunk for the faction ...
                    &&
                    !Plots::get()->isConnectedPlot($chunk, $newFaction)
                    // ... and either claims must always be connected or there is at least one normal faction among the old factions ...
                    &&
                    (!Settings::get("Settings.claimsCanBeUnconnectedIfOwnedByOtherFaction", true) || $currentFactionIsNormal)
                ) {
                    if (Settings::get("Settings.claimsCanBeUnconnectedIfOwnedByOtherFaction", false)) {
                        $player->sendMessage(Text::parse("<b>You can only claim additional land which is connected to your first claim or controlled by another faction!"));
                    } else {
                        $player->sendMessage(Text::parse("<b>You can only claim additional land which is connected to your first claim!"));
                    }
                    $event->setCancelled(true);
                    return;
                }
            }

            // Old faction..
            $oldFaction = $chunk->getOwnerFaction();

            // ... that is an actual faction ...
            if (!$oldFaction->isNone()) {

                // ... for which the mplayer lacks permission ...
                if (Perm::getPermTerritory()->has($player, $oldFaction)) {

                    // ... consider all reasons to forbid "overclaiming/warclaiming" ...

                    // ... claiming from others may be forbidden ...
                    if (!Settings::get("Settings.claimingFromOthersAllowed", true)) {
                        $player->sendMessage(Text::parse("<b>You may not claim land from others."));
                        $event->setCancelled(true);
                        return;
                    }

                    // ... the relation may forbid ...
                    if (RelationUtil::isAtLeast($oldFaction->getRelationTo($newFaction), Rel::TRUCE)) {
                        $player->sendMessage(Text::parse("<b>You can't claim this land due to your relation with the current owner."));
                        $event->setCancelled(true);
                        return;
                    }

                    // ... the old faction might not be inflated enough ...
                    if ($oldFaction->getPowerRounded() > $oldFaction->getLandCount() - 1) {
                        $player->sendMessage(Text::parse("%s<i> owns this land and is strong enough to keep it.", $oldFaction->getName()));
                        $event->setCancelled(true);
                        return;
                    }

                    // ... and you might be trying to claim without starting at the border ...
                    if (!Plots::get()->isBorderPlot($chunk)) {
                        $player->sendMessage(Text::parse("<b>You must start claiming land at the border of the territory."));
                        $event->setCancelled(true);
                        return;
                    }

                    // ... otherwise you may claim from this old faction even though you lack explicit permission from them.
                }
            }
        }
    }


    // -------------------------------------------- //
    // POWER LOSS ON DEATH
    // -------------------------------------------- //

    /**
     * @param PlayerDeathEvent $event
     * @priority NORMAL
     */
    public function powerLossOnDeath(PlayerDeathEvent $event)
    {
        // If player dies...
        $player = $event->getEntity();
        if (!($player instanceof Player)) return;

        $fplayer = FPlayer::get($player);

        // ... and powerloss can happen here ...
        $faction = Plots::get()->getFactionAt($player);

        if (!$faction->getFlag(Flag::POWER_LOSS)) {
            $player->sendMessage(Text::parse("<i>You didn't lose any power since the territory you died in works that way."));
            return;
        }

        if (!in_array($player->getLevel()->getName(), Settings::get("Settings.worldsPowerLossEnabled", []), true)) {
            $player->sendMessage(Text::parse("<i>You didn't lose any power due to the world you died in."));
            return;
        }

        // ... alter the power ...
        echo "Player power: ".$fplayer->getPower()."\n";
        echo "Power per death: ".$fplayer->getPowerPerDeath()."\n";
        echo "Total: ".($fplayer->getPower() + $fplayer->getPowerPerDeath())."\n"; 

        $newPower = $fplayer->getPower() + $fplayer->getPowerPerDeath();

        $event = new PlayerPowerChangeEvent($fplayer, $newPower, PlayerPowerChangeEvent::DEATH);
        FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);

        if ($event->isCancelled()) return;
        $newPower = $event->getNewPower();

        $fplayer->setPower($newPower);

        // ... and inform the player.
        // TODO: A progress bar here would be epic :)
        $player->sendMessage(Text::parse("<i>Your power is now <h>%var0 / %var1", $fplayer->getPower(), $fplayer->getPowerMax()));
    }

    // -------------------------------------------- //
    // REMOVE PLAYER DATA WHEN BANNED
    // -------------------------------------------- //

    /*@EventHandler(priority = EventPriority.MONITOR, ignoreCancelled = true)
    public function onPlayerKick(PlayerKickEvent event)
    {
        // If a player was kicked from the server ...
        $player = $event->getPlayer();

        // ... and if the if player was banned (not just kicked) ...
        //if (!$event->getReason().equals("Banned by admin.")) return;
        if (!player.isBanned()) return;
        
        // ... and we remove player data when banned ...
        if (!MConf.get().removePlayerWhenBanned) return;
        
        // ... get rid of their stored info.
        MPlayer mplayer = MPlayerColl.get().get(player, false);
        if (mplayer == null) return;
        
        if (mplayer.getRole() == Rel.LEADER)
        {
            mplayer.getFaction().promoteNewLeader();
        }
        
        mplayer.leave();
        mplayer.detach();
    }*/
    # TODO

    // -------------------------------------------- //
    // VISUALIZE UTIL
    // -------------------------------------------- //

    // -------------------------------------------- //
    // DENY COMMANDS
    // -------------------------------------------- //


    // -------------------------------------------- //
    // FLAG: EXPLOSIONS
    // -------------------------------------------- //

    /**
     * @param EntityExplodeEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockExplosion(EntityExplodeEvent $event)
    {
        // Prepare some variables:
        // Current faction
        $faction = null;
        // Caching to speed things up.
        $faction2allowed = [];

        $entity = $event->getEntity();
        // If an explosion occurs at a location ...
        $pos = $entity->getPosition();

        // Check the entity. Are explosions disabled there? 
        $faction = Plots::get()->getFactionAt($pos);
        $allowed = $faction->isExplosionsAllowed();
        if ($allowed == false) {
            $event->setCancelled(true);
            return;
        }
        $faction2allowed[$faction->getId()] = $allowed;

        // Individually check the flag state for each block
        $blocks = $event->getBlockList();
        foreach ($blocks as $block) {
            $faction = Plots::get()->getFactionAt($block);
            $allowed = isset($faction2allowed[$faction->getId()]) ? $faction2allowed[$faction->getId()] : NULL;
            if ($allowed == null) {
                $allowed = $faction->isExplosionsAllowed();
                $faction2allowed[$faction->getId()] = $allowed;
            }

            if ($allowed == false) unset($blocks[array_search($block, $blocks, true)]);
        }
        $event->setBlockList($blocks);
    }

    // -------------------------------------------- //
    // FLAG: ENDERGRIEF
    // -------------------------------------------- //

    # TODO

    // -------------------------------------------- //
    // FLAG: ZOMBIEGRIEF
    // -------------------------------------------- //

    # TODO

    // -------------------------------------------- //
    // FLAG: FIRE SPREAD
    // -------------------------------------------- //

    /**
     * @param BlockSpreadEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockFireSpreadEvent(BlockSpreadEvent $event)
    {
        // If a block is burning ...
        if ($event->getBlock()->getId() === Block::FIRE)
            // ... consider blocking it.
            $this->blockFireSpread($event->getBlock(), $event);
    }

    public function blockFireSpread(Block $block, Cancellable $event)
    {
        // If the faction at the block has firespread disabled ...
        $faction = Plots::get()->getFactionAt($block);

        if ($faction->getFlag(Flag::FIRE_SPREAD)) return;

        // then cancel the event
        $event->setCancelled(true);
    }

    // -------------------------------------------- //
    // FLAG: BUILD
    // -------------------------------------------- //

    /**
     * @param BlockBreakEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockBreak(BlockBreakEvent $event)
    {
        if ($this->canPlayerBuildAt($event->getPlayer(), $event->getBlock())) return;

        $event->setCancelled(true);
    }

    public static function canPlayerBuildAt(Player $fplayer, Position $pos)
    {
        $fplayer = FPlayer::get($fplayer);
        if ($fplayer == null) return false;

        $name = $fplayer->getName();
        if (in_array($name, Settings::get("Settings.playersWhoBypassAllProtection", []), true)) return true;

        if ($fplayer->isOverriding()) return true;

        if (!Perm::getPermById(Perm::BUILD)->has($fplayer, Plots::get()->getFactionAt($pos)) && Perm::getPermById(Perm::PAINBUILD)->has($fplayer, Plots::get()->getFactionAt($pos))) {
            if (true) {
                $hostFaction = Plots::get()->getFactionAt($pos);
                $fplayer->sendMessage(Text::parse("<b>It is painful to build in the territory of %var0<b>.", $hostFaction->getName()));
                $fplayer = $fplayer->getPlayer();
                if ($fplayer != null) {
                    $damage = Settings::get("Settings.actionDeniedPainAmount", 1);
                    $fplayer->attack($damage, new EntityDamageEvent($fplayer, EntityDamageEvent::CAUSE_CUSTOM, $damage));
                }
            }
            return true;
        }

        $factionHere = Plots::get()->getFactionAt($pos);
        return Perm::getPermById(Perm::BUILD)->has($fplayer, $factionHere);
    }

    /**
     * @param SignChangeEvent $event
     * @ignoreCancelled true
     * @priority NORMAL
     */
    public function signChange(SignChangeEvent $event)
    {
        if ($this->canPlayerBuildAt($event->getPlayer(), $event->getBlock())) return;

        $event->setCancelled(true);
    }


    /**
     * @param BlockPlaceEvent $event
     * @ignoreCancelled true
     * @priority NORMAL
     */
    public function blockPlace(BlockPlaceEvent $event)
    {
        if ($this->canPlayerBuildAt($event->getPlayer(), $event->getPlayer())) return;

        $event->setCancelled(true);
    }

    /**
     * @param BlockSpreadEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockLiquidFlow(BlockSpreadEvent $event)
    {
        //echo "Liquid is flowing\n";
        if (!Settings::get("Settings.protectionLiquidFlowEnabled", true)) return;

        // Prepare fields
        $fromBlock = $event->getBlock();
        if( !($fromBlock instanceof Liquid) ) return;
        $fromCX = $fromBlock->getX() >> 4;
        $fromCZ = $fromBlock->getZ() >> 4;
        $toBlock = $event->getNewState();
        $toBlock->setComponents($fromBlock->x, $fromBlock->y, $fromBlock->z);
        $toBlock->level = $fromBlock->level;
        $toCX = $toBlock->getX() >> 4;
        $toCZ = $toBlock->getY() >> 4;

        // If a liquid (or dragon egg) moves from one chunk to another ...
        if ($toCX == $fromCX && $toCZ == $fromCZ) return;

        $plotFrom = new Plot($fromBlock);
        $plotTo = new Plot($toBlock);
        $fromFaction = $plotFrom->getOwnerFaction();
        $toFaction = $plotTo->getOwnerFaction();

        // ... and the chunks belong to different factions ...
        if ($fromFaction === $toFaction) return;

        // ... and the faction "from" can not build at "to" ...
        if (Perm::getPermBuild()->factionHas($fromFaction, $toFaction)) return;

        // ... cancel!
        $event->setCancelled(true);
    }

    // -------------------------------------------- //
    // ASSORTED BUILD AND INTERACT
    // -------------------------------------------- //

    /**
     * @param EntityDamageEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerDamageEntity(EntityDamageEvent $event)
    {
        if (!($event instanceof EntityDamageByEntityEvent)) return;
        /** @var EntityDamageByEntityEvent $event */
        $entity = $event->getEntity();
        // If a player ...
        $edamager = $event->getDamager();

        if (!($edamager instanceof Player)) return;
        $player = $edamager;

        // ... and the player can't build there ...
        if($entity instanceof Creeper)
            if ($this->canPlayerBuildAt($player, $entity)) return;

        // ... then cancel the $event->
        $event->setCancelled(true);
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerInteract(PlayerInteractEvent $event)
    {

        $block = $event->getBlock();
        $player = $event->getPlayer();
        if ($block == null) return;  // clicked in air, apparently

        if (!$this->canPlayerUseBlock($player, $block)) {
            $event->setCancelled(true);
            return;
        }

        if ($event->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;  // only interested on right-clicks for below

        if (!$this->playerCanUseItemHere($player, $block, $event->getItem())) {
            $event->setCancelled(true);
            return;
        }
    }

    public static function canPlayerUseBlock(Player $player, Block $block)
    {
        $name = $player->getName();
        if (in_array($name, Settings::get("Settings.playersWhoBypassAllProtection", []), true)) return true;

        $me = FPlayer::get($player);
        if ($me->isOverriding()) return true;
        $id = $block->getId();
        $factionHere = Plots::get()->getFactionAt($block);

        //if (in_array($id, Settings::get("Settings.materialsEditOnInteract", []), true) && !Perm::getPermBuild()->has($me, $factionHere)) return false;
        //if (in_array($id, Settings::get("Settings.materialsContainer", []), true) && !Perm::getPermContainer()->has($me, $factionHere)) return false;
        //if (in_array($id, Settings::get("Settings.materialsDoors", []), true) && !Perm::getPermDoor()->has($me, $factionHere)) return false;
        if ($id === Block::STONE_BUTTON && !Perm::getPermById(Perm::BUTTON)->has($me, $factionHere)) return false;
        if ($id === Block::LEVER && !Perm::getPermById(Perm::LEVEL)->has($me, $factionHere)) return false;
        if ($id === Block::DOOR_BLOCK && !Perm::getPermById(Perm::DOOR)->has($me, $factionHere)) return false;//return false;
        return true;
    }

    public static function playerCanUseItemHere(Player $player, Position $pos, Item $item)
    {
        if (!in_array($item->getId(), Settings::get("Settings.materialsEditTools", []), true) && !in_array($item->getId(), Settings::get("materialsEditToolsDupeBug", []), true)) return true;

        $name = $player->getName();
        if (in_array($name, Settings::get("Settings.playersWhoBypassAllProtection", []), true)) return true;

        $fplayer = FPlayer::get($player);
        if ($fplayer->isOverriding()) return true;

        return Perm::getPermBuild()->has($fplayer, Plots::get()->getFactionAt($pos));
    }

    // For some reason onPlayerInteract() sometimes misses bucket events depending on distance (something like 2-3 blocks away isn't detected),
    // but these separate bucket events below always fire without fail

    /**
     * @param PlayerBucketEmptyEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerBucketEmpty(PlayerBucketEmptyEvent $event)
    {
        $block = $event->getBlockClicked();
        $player = $event->getPlayer();

        if (self::playerCanUseItemHere($player, $block, $event->getBucket())) return;

        $event->setCancelled(true);
    }

    /**
     * @param PlayerBucketFillEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerBucketFill(PlayerBucketFillEvent $event)
    {
        $block = $event->getBlockClicked();
        $player = $event->getPlayer();

        if ($this->playerCanUseItemHere($player, $block, $event->getBucket())) return;

        $event->setCancelled(true);
    }

    // -------------------------------------------- //
    // TELEPORT TO HOME ON DEATH
    // -------------------------------------------- //

    /**
     * @param PlayerRespawnEvent $event
     * @priority MONITOR
     */
    public function teleportToHomeOnDeath(PlayerRespawnEvent $event)
    {
        // If a player is respawning ...
        $player = $event->getPlayer();
        $fplayer = FPlayer::get($player);

        // ... homes are enabled, active and at this priority ...
        if (!Settings::get("Settings.homesEnabled", true)) return;
        if (!Settings::get("Settings.homesTeleportToOnDeathActive", true)) return;

        // ... and the player has a faction ...
        $faction = $fplayer->getFaction();
        if ($faction->isNone()) return;

        // ... and the faction has a home ...
        $home = $faction->getHome();
        if ($home == null) return;

        // ... then use it for the respawn location.
        $event->setRespawnPosition($home);
    }

}