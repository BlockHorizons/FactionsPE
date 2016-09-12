<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasPerm;
use factions\entity\Flag;
use factions\entity\Perm;
use factions\event\player\PlayerHomeTeleportEvent;
use factions\FactionsPE;
use factions\entity\FPlayer;
use factions\objs\Plots;
use factions\objs\Rel;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class HomeSubCommand extends Command
{

    public function __construct(FactionsPE $main)
    {
        parent::__construct(
            $main, "home", "Teleport to faction home", FactionsPE::HOME, [], [] # Requirements set below
        );

        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
        $this->addRequirement(new ReqHasPerm(Perm::getPermHome()));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        /** @var FPlayer $fplayer */
        $fplayer = FPlayer::get($sender);
        if (!Settings::get("homesTeleportCommandEnabled", true)) {
            $sender->sendMessage(Text::parse("command.home.disabled"));
            return true;
        }

        if (!$fplayer->getFaction()->hasHome()) {
            $sender->sendMessage(Text::parse('command.home.no.valid', $fplayer->getFaction()->describeTo($fplayer)));
            if ($fplayer->hasPermission(Perm::getPermById(Perm::SETHOME)) and $sender->hasPermission(FactionsPE::SETHOME)) {
                $sender->sendMessage(Text::parse('command.home.advice', $this->getPlugin()->getServer()->getCommandMap()->getCommand("faction")->getChild("sethome")->getUsage()));
            }
            return true;
        }
        if ( ! Settings::get("homesTeleportAllowedFromEnemyTerritory", false) && $fplayer->isInEnemyTerritory())
        {
            $sender->sendMessage(Text::parse("You cannot teleport to %var0 while in the territory of an enemy faction.", "faction home"));
            return true;
        }
        if ( ! Settings::get("homesTeleportAllowedFromDifferentWorld", true) && $fplayer->getFaction()->getHome()->getLevel() !== $sender->getLevel())
        {
            $sender->sendMessage(Text::parse("You can not teleport to your faction home while in different world"));
            return true;
        }

        $factionHere = Plots::get()->getFactionAt($sender);
        // if player is not in a safe zone or their own faction territory, only allow teleport if no enemies are nearby
        if
        (
            ($max = Settings::get("homesTeleportAllowedEnemyDistance", 10)) > 0
            &&
            $factionHere->getFlag(Flag::PVP)
            &&
            (
                ! $fplayer->isInOwnTerritory()
                ||
                (
                    $fplayer->isInOwnTerritory()
                    &&
                    ! Settings::get("homesTeleportIgnoreEnemiesIfInOwnTerritory", false)
                )
            )
        ) {
            foreach(FPlayer::getAllOnline() as $otherPlayer)
            {
                if($fplayer->getRelationTo($otherPlayer) !== Rel::ENEMY) continue;
                    if($fplayer->getPosition()->distance($otherPlayer->getPosition()) < $max) {
                        $sender->sendMessage(Text::parse("command.home.enemies.nearby", $max));
                        return true;
                    }
            }

        }

        $event = new PlayerHomeTeleportEvent($sender, $fplayer->getFaction()->getHome());
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if($event->isCancelled()) return true;

        $home = $event->getDestination();
        $sender->teleport($home->getLevel()->getSafeSpawn());
        $sender->teleport($home);
        $sender->sendMessage(Text::parse("command.home.success"));
        return true;
    }

}