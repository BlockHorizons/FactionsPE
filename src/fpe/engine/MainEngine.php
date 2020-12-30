<?php
/*
 *     FactionsPE: PocketMine-MP Plugin
 *     Copyright (C) 2016   Chris Prime
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.   See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace fpe\engine;

use fpe\entity\Member;
use fpe\entity\Plot;
use fpe\event\LandChangeEvent;
use fpe\event\member\MemberPowerChangeEvent;
use fpe\event\member\MemberTraceEvent;
use fpe\FactionsPE;
use fpe\flag\Flag;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\manager\Plots;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\utils\Gameplay;
use fpe\utils\Text;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
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
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;

class MainEngine extends Engine
{

    const TOUCH_SENSITIVE = [
        Block::CAKE_BLOCK,
        Block::SNOW,
    ];

    const CONTAINERS = [
        Block::CHEST,
        Block::TRAPPED_CHEST,
        Block::DROPPER,
        Block::BREWING_STAND_BLOCK,
        Block::ENCHANTING_TABLE,
    ];

    const DOORS = [
        BlockIds::WOODEN_DOOR_BLOCK,
        BlockIds::TRAPDOOR,
        BlockIds::FENCE_GATE,
        BlockIds::NETHER_BRICK_FENCE,
        BlockIds::IRON_TRAPDOOR,
        BlockIds::SPRUCE_FENCE_GATE,
        BlockIds::BIRCH_FENCE_GATE,
        BlockIds::JUNGLE_FENCE_GATE,
        BlockIds::DARK_OAK_FENCE_GATE,
        BlockIds::ACACIA_FENCE_GATE,
        BlockIds::SPRUCE_DOOR_BLOCK,
        BlockIds::BIRCH_DOOR_BLOCK,
        BlockIds::JUNGLE_DOOR_BLOCK,
        BlockIds::ACACIA_DOOR_BLOCK,
        BlockIds::DARK_OAK_DOOR_BLOCK,
    ];

    const EDIT_TOOLS = [];

    public function onPlayerPrelogin(PlayerPreLoginEvent $event)
    {
        $m = Members::get($event->getPlayer(), true);
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $m = Members::get($event->getPlayer(), true);
        if ($m->hasFaction()) {
            if (!$m->getFaction()->getTimeJoined()) {
                $m->getFaction()->startCountingOnlineTime();
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        Members::detach(Members::get($event->getPlayer()));
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $member = Members::get($event->getPlayer());
        $cz = $member->getPlayer()->getZ() >> Plots::$CHUNK_SIZE;
        $cx = $member->getPlayer()->getX() >> Plots::$CHUNK_SIZE;
        if (
            $cx !== $member->chunkPos[0] ||
            $cz !== $member->chunkPos[1]
        ) {
            # Player moved from plot to plot
            $member->chunkPos = [$cx, $cz];
            # Call event
            $event = new MemberTraceEvent($member, new Plot($event->getFrom()), new Plot($event->getTo()));
            $this->getMain()->getServer()->getPluginManager()->callEvent($event);
        }
    }

    public function onPlayerTrace(MemberTraceEvent $event)
    {
        /** @var Member $member */
        $member = $event->getMember();

        $faction = $event->getTo()->getOwnerFaction();
        // Update current location
        $member->setFactionHereId($faction->getId());

        if (!$event->sameOwner() && Gameplay::get("send-plot-faction-description", true)) {

            $event->getMember()->getPlayer()->sendTip(Localizer::translatable("plot-faction-tip", [
                "color" => $event->getMember()->getColorTo($faction),
                "faction" => $faction->getName(),
                "description" => $faction->hasDescription() ? $faction->getDescription() : "~",
            ]));

            if ($member->isFlying() && !$member->isOverriding()) {
                if (!$event->membersLand()) {
                    $member->setFlying(false);
                } else {
                    $member->setFlying(true);
                }
            }

        }
        if ($member->isAutoClaiming()) {
            $af = $member->getAutoClaimFaction();
            if (Plots::claim($af, new Plot($member->getPlayer()), $member, false)) {
                $member->getPlayer()->sendTip(Text::parse("<green>CLAIMED"));
            }
        }
    }

    public function showMotdOnJoin(PlayerJoinEvent $event)
    {
        if (!Gameplay::get("show-motd-on-join", true)) {
            $member = Members::get($event->getPlayer());
            $faction = $member->getFaction();
            // ... if there is a motd ...
            if (!$faction->hasMotd()) {
                return;
            }

            // ... then prepare the message ...
            $message = $faction->getMotd();
            // ... and send to the player.
            $member->sendMessage($message);
        }
    }

    /**
     * @param LandChangeEvent $event
     * @priority LOW
     * @ignoreCancelled true
     */
    public function onChunksChange(LandChangeEvent $event)
    {
        // For security reasons we block the chunk change on any error since an error might block security checks from happening.
        try {
            if (!$this->canPlotOwnershipChange($event)) {
                $event->setCancelled(true);
            }
        } catch (\Exception $throwable) {
            $event->setCancelled(true);
            // echo $throwable->getTraceAsString();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function canPlotOwnershipChange($event): bool
    {
        if (!$event instanceof LandChangeEvent) {
            throw new \InvalidArgumentException("Argument 1 passed to " . __CLASS__ . "::" . __METHOD__ . " must be instanceof " . LandChangeEvent::class . ", '" . Text::toString($event) . "' given");
        }

        // Args
        $player = $event->getPlayer();
        if (!$player) return true;

        // If player is in Overriding mode then ignore all logics below
        if ($player->isOverriding()) {
            return true;
        }

        $newFaction = $event->getFaction();
        $plot = $event->getPlot();
        $currentFaction = Plots::getFactionAt($plot);
        // CALC: Is there at least one normal faction among the current ones?
        $currentFactionIsNormal = $currentFaction->isNormal();

        if ($event->getChangeType() === LandChangeEvent::CLAIM) {
            // If the new faction is normal (not wilderness/none), meaning if we are claiming for a faction ...
            if ($newFaction->isNormal()) {
                $world = $plot->getLevel();

                // Can't claim a plot in this level (not specified in config)
                if (!in_array($world->getFolderName(), Gameplay::get("worlds-claiming-enabled", []), true)) {
                    $worldName = $world->getFolderName();
                    $player->sendMessage(Localizer::translatable("claiming-disabled-in-world", [$worldName]));
                    return false;
                }
                // ... ensure we have permission to alter the territory of the new faction ...
                if (!Permissions::getById(Permission::TERRITORY)->has($player, $newFaction)) {
                    // NOTE: No need to send a message. We send message from the permission check itself.
                    $player->sendMessage(Localizer::translatable("no-perm-to-claim-for", [$newFaction->getName()]));
                    return false;
                }
                // ... ensure the new faction has enough players to claim ...
                if (count($newFaction->getMembers()) < $m = Gameplay::get("claims-require-min-faction-members", 1)) {
                    $player->sendMessage(Localizer::translatable("not-enough-members-to-claim", [$m]));
                    return false;
                }
                $claimedLandCount = $newFaction->getLandCount();
                if (!$newFaction->getFlag(Flag::INFINITY_POWER)) {
                    // ... ensure the claim would not bypass the global max limit ...
                    if (Gameplay::get("claimed-lands-max", 99999999) != 0 && $claimedLandCount + 1 > Gameplay::get("claimed-lands-max", 999999999)) {
                        $player->sendMessage(Localizer::translatable("claim-limit-reached"));
                        return false;
                    }
                }
                // ... ensure the claim would not bypass the faction power ...
                if (($claimedLandCount + 1) * Gameplay::get("power.per-claim", 5) > $newFaction->getPower(true)) {
                    $player->sendMessage(Localizer::translatable("not-enough-power-to-claim"));
                    return false;
                }
                // ... ensure claims are properly connected ...
                if (
                    // If claims must be connected ...
                    Gameplay::get("claims-must-be-connected", true)
                    // ... and this faction already has claimed something on this map (meaning it's not their first claim) ...
                    && $newFaction->getPlotsCountInLevel($world) > 0
                    // ... and none of the chunks are connected to an already claimed chunk for the faction ...
                    && Plots::isConnectedPlot($plot, $newFaction)
                    // ... and either claims must always be connected or there is at least one normal faction among the old factions ...
                    && (!Gameplay::get("claims-can-be-unconnected-if-owned-by-other-faction", true) || $currentFactionIsNormal)
                ) {
                    if (Gameplay::get("claims-can-be-unconnected-if-owned-by-other-faction", true)) {
                        $player->sendMessage(Localizer::translatable("plot-must-be-connected-to-owned-one"));
                    } else {
                        $player->sendMessage(Localizer::translatable("plot-must-be-connected-faction-territory"));
                    }
                    return false;
                }
            }
            // Old faction..
            // ... that is an actual faction ...
            if (!$currentFaction->isNone()) {
                // ... for which the player lacks permission ...
                if (!Permissions::getById(Permission::TERRITORY)->has($player, $currentFaction)) {
                    // ... consider all reasons to forbid "overclaiming/warclaiming" ...
                    // ... claiming from others may be forbidden ...
                    if (!Gameplay::get("claiming-from-others-allowed", true)) {
                        $player->sendMessage(Localizer::translatable("claim-from-others-not-allowed"));
                        return false;
                    }
                    // ... the relation may forbid ...
                    if (Relation::isAtLeast($currentFaction->getRelationTo($newFaction), Relation::TRUCE)) {
                        $player->sendMessage(Localizer::translatable("cant-claim-due-to-relation"));
                        $event->setCancelled(true);
                        return false;
                    }
                    // ... the old faction might not be inflated enough ...
                    if (!$currentFaction->hasLandInflation()) {
                        $player->sendMessage(Localizer::translatable("cant-claim-owner-too-strong", [$currentFaction->getName()]));
                        $event->setCancelled(true);
                        return false;
                    }
                    // ... and you might be trying to claim without starting at the border ...
                    if (!Plots::isBorderPlot($plot)) {
                        $player->sendMessage(Localizer::translatable("must-start-claim-at-border"));
                        $event->setCancelled(true);
                        return false;
                    }
                }
            }
        } elseif ($event->getChangeType() === LandChangeEvent::UNCLAIM) {
            // ... ensure we have permission to alter the territory of the new faction ...
            if (!Permissions::getById(Permission::TERRITORY)->has($player, $currentFaction)) {
                // NOTE: No need to send a message. We send message from the permission check itself.
                $player->sendMessage(Localizer::translatable("no-perm-to-claim-for", [$newFaction->getName()]));
                return false;
            }
        }
        return true; // You can claim!
    }

    /**
     * # TODO
     */
    public function teleportToHomeOnRespawn(PlayerRespawnEvent $event)
    {
        $player = Members::get($event->getPlayer());
        if ($player->hasFaction() && $player->getFaction()->hasHome()) {
            $event->setRespawnPosition($player->getFaction()->getHome());
            $player->sendMessage(Text::parse("<b>Teleported to faction home"));
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
        if (!($player instanceof Player)) {
            return;
        }

        $fplayer = Members::get($player);
        // ... and powerloss can happen here ...
        $faction = Plots::getFactionAt($player);
        if (!$faction->getFlag(Flag::POWER_LOSS)) {
            $player->sendMessage(Localizer::translatable("no-powerloss-due-to-faction"));
            return;
        }
        if (!in_array($player->getLevel()->getFolderName(), Gameplay::get("worlds-power-loss-enabled", []), true)) {
            $player->sendMessage(Localizer::translatable("no-powerloss-due-to-world"));
            return;
        }
        // ... alter the power ...
        $newPower = $fplayer->getPower() - $fplayer->getPowerPerDeath();
        $event = new MemberPowerChangeEvent($fplayer, $newPower, MemberPowerChangeEvent::DEATH);
        FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) {
            return;
        }

        $newPower = $event->getNewPower();
        $fplayer->setPower($newPower);
        // ... and inform the player.
        // TODO: A progress bar here would be epic :)
        $player->sendMessage(Localizer::translatable("power-level-inform-on-death", [$fplayer->getPower(), $fplayer->getPowerMax()]));
    }

    /**
     * @param EntityExplodeEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockExplosion(EntityExplodeEvent $event)
    {
        $faction2allowed = [];
        $entity = $event->getEntity();
        // If an explosion occurs at a location ...
        $pos = $entity->getPosition();
        $faction = Plots::getFactionAt($pos);
        // Check the entity. Are explosions disabled there?
        $allowed = $faction->isExplosionsAllowed();
        if (!$allowed) {
            $event->setCancelled(true);
            return;
        }
        $faction2allowed[$faction->getId()] = $allowed;
        $blocks = $event->getBlockList();
        foreach ($blocks as $block) {
            $faction = Plots::getFactionAt($block);
            $allowed = $faction2allowed[$faction->getId()] ?? null;
            if ($allowed === null) {
                $allowed = $faction->isExplosionsAllowed();
                $faction2allowed[$faction->getId()] = $allowed;
            }
            if ($allowed === false) {
                unset($blocks[array_search($block, $blocks, true)]);
            }

        }
        $event->setBlockList($blocks);
    }

    /**
     * @param BlockSpreadEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockFireSpreadEvent(BlockSpreadEvent $event)
    {
        // If a block is burning ...
        if ($event->getBlock()->getId() === Block::FIRE) // ... consider blocking it.
        {
            $this->blockFireSpread($event->getBlock(), $event);
        }

    }

    public function blockFireSpread(Block $block, Cancellable $event)
    {
        // If the faction at the block has firespread disabled ...
        $faction = Plots::getFactionAt($block);
        if ($faction->getFlag(Flag::FIRE_SPREAD)) {
            return;
        }

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
        if (self::canPlayerBuildAt($event->getPlayer(), $event->getBlock())) {
            return;
        }

        $event->setCancelled(true);
        $event->getPlayer()->sendMessage(Localizer::translatable("cant-edit-land-here", [
            "faction" => ($f = Plots::getFactionAt($event->getBlock()))->getName(),
            "rel-color" => $f->getColorTo(Members::get($event->getPlayer())),
        ]));
    }

    public static function canPlayerBuildAt(Player $player, Position $pos): bool
    {
        $member = Members::get($player);
        $name = $member->getName();
        if (in_array($name, Gameplay::get("players-who-bypass-all-protection", []), true)) {
            return true;
        }

        if ($member->isOverriding()) {
            return true;
        }

        if (!Permissions::getById(Permission::BUILD)->has($member, Plots::getFactionAt($pos)) && Permissions::getById(Permission::PAINBUILD)->has($member, Plots::getFactionAt($pos))) {
            $hostFaction = Plots::getFactionAt($pos);
            $player->sendMessage(Localizer::translatable("painbuild-warning", [$hostFaction->getName()]));
            $damage = Gameplay::get("action-denied-pain-amount", 1);
            $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, $damage));
            return true;
        }
        $factionHere = Plots::getFactionAt($pos);
        return Permissions::getById(Permission::BUILD)->has($member, $factionHere);
    }

    /**
     * @param SignChangeEvent $event
     * @ignoreCancelled true
     * @priority NORMAL
     */
    public function signChange(SignChangeEvent $event)
    {
        if (self::canPlayerBuildAt($event->getPlayer(), $event->getBlock())) {
            return;
        }

        $event->setCancelled(true);
    }

    /**
     * @param BlockPlaceEvent $event
     * @ignoreCancelled true
     * @priority NORMAL
     */
    public function blockPlace(BlockPlaceEvent $event)
    {
        if (self::canPlayerBuildAt($event->getPlayer(), $event->getBlock())) {
            return;
        }

        $event->setCancelled(true);
    }

    /**
     * @param BlockSpreadEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function blockLiquidFlow(BlockSpreadEvent $event)
    {
        if (!Gameplay::get("protection-liquid-flow-enabled", true)) {
            return;
        }

        // Prepare fields
        $fromBlock = $event->getBlock();
        if (!($fromBlock instanceof Liquid)) {
            return;
        }

        $fromCX = $fromBlock->getX() >> Plots::$CHUNK_SIZE;
        $fromCZ = $fromBlock->getZ() >> Plots::$CHUNK_SIZE;
        $toBlock = $event->getNewState();
        $toBlock->setComponents($fromBlock->x, $fromBlock->y, $fromBlock->z);
        $toBlock->level = $fromBlock->level;
        $toCX = $toBlock->getX() >> Plots::$CHUNK_SIZE;
        $toCZ = $toBlock->getY() >> Plots::$CHUNK_SIZE;
        // If a liquid (or dragon egg) moves from one chunk to another ...
        if ($toCX == $fromCX && $toCZ == $fromCZ) {
            return;
        }

        $plotFrom = new Plot($fromBlock);
        $plotTo = new Plot($toBlock);
        $fromFaction = $plotFrom->getOwnerFaction();
        $toFaction = $plotTo->getOwnerFaction();
        // ... and the chunks belong to different factions ...
        if ($fromFaction === $toFaction) {
            return;
        }

        // ... and the faction "from" can not build at "to" ...
        if (Permissions::getById(Permission::BUILD)->factionHas($fromFaction, $toFaction)) {
            return;
        }

        // ... cancel!
        $event->setCancelled(true);
    }

    /**
     * @param EntityDamageEvent $event
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerDamageEntity(EntityDamageEvent $event)
    {
        if (!$event instanceof EntityDamageByEntityEvent) {
            return;
        }

        $entity = $event->getEntity();
        // If a player ...
        $edamager = $event->getDamager();
        if (!($edamager instanceof Player)) {
            return;
        }

        $player = $edamager;
        // ... and the player can't build there ...
        if ($entity instanceof Creeper) {
            if (self::canPlayerBuildAt($player, $entity)) {
                return;
            } else {
                $event->setCancelled(true);
            }
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if (!$this->canPlayerUseBlock($player, $block)) {
            $event->setCancelled(true);
            return;
        }
        if ($event->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return;
        }
        // only interested on right-clicks for below
        if (!self::playerCanUseItemHere($player, $block, $event->getItem())) {
            $event->setCancelled(true);
            return;
        }
    }

    public static function canPlayerUseBlock(Player $player, Block $block)
    {
        if (in_array(strtolower($player->getName()), Gameplay::get("players-who-bypass-all-protection", []), true)) {
            return true;
        }

        $me = Members::get($player);
        if ($me->isOverriding()) {
            return true;
        }

        $id = $block->getId();
        $factionHere = Plots::getFactionAt($block);

        if (in_array($id, self::TOUCH_SENSITIVE, true) && !Permissions::getById(Permission::BUILD)->has($me, $factionHere)) {
            return false;
        }

        if (in_array($id, self::CONTAINERS, true) && !Permissions::getById(Permission::CONTAINER)->has($me, $factionHere)) {
            return false;
        }

        if (in_array($id, self::DOORS, true) && !Permissions::getById(Permission::DOOR)->has($me, $factionHere)) {
            return false;
        }

        if ($id === Block::STONE_BUTTON && !Permissions::getById(Permission::BUTTON)->has($me, $factionHere)) {
            return false;
        }

        if ($id === Block::LEVER && !Permissions::getById(Permission::LEVER)->has($me, $factionHere)) {
            return false;
        }

        return true;
    }

    public static function playerCanUseItemHere(Player $player, Position $pos, Item $item)
    {
        if (!in_array($item->getId(), self::EDIT_TOOLS, true)) {
            if (!in_array($item->getId(), Gameplay::get("materials-edit-tools-dupe-bug", []), true)) {
                return true;
            }
        }
        if (in_array(strtolower($player->getName()), Gameplay::get("players-who-bypass-all-protection", []), true)) {
            return true;
        }
        $fplayer = Members::get($player);
        if ($fplayer->isOverriding()) {
            return true;
        }

        return Permissions::getById(Permission::BUILD)->has($fplayer, Plots::getFactionAt($pos));
    }

}
