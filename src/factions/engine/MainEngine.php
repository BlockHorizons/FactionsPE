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
use factions\relation\Permission;
use factions\manager\Plots;
use factions\manager\Members;
use factions\event\LandChangeEvent;
use factions\event\player\PlayerPowerChangeEvent;
use factions\relation\Relation;
use factions\utils\Gameplay;
use factions\utils\Text;
use factions\FactionsPE;

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
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;

class MainEngine extends Engine
{
   
   public function onPlayerLogin(PlayerPreLoginEvent $event) {
   	Members::get($event->getPlayer())->updateLastActivity();
   }
   
   public function onPlayerJoin(PlayerJoinEvent $event) {

   }

   public function onPlayerQuit(PlayerQuitEvent $event) {
   		$member = Members::get($event->getPlayer());
   		$member->setLastPlayed(time());
   		$member->save();
   		Members::detach($member);
   } 

}