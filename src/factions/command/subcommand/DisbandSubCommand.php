<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/19/16
 * Time: 7:08 PM
 */

namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\entity\Faction;
use factions\entity\Flag;
use factions\objs\Rel;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\event\faction\FactionDisbandEvent;
use factions\event\player\PlayerMembershipChangeEvent;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use factions\command\requirement\ReqHasPerm;

class DisbandSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "disband", "Disband your faction", "factions.command.disband", []);
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasPerm(Perm::getPermById(Perm::DISBAND)));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        $fsender = FPlayer::get($sender);
        $own = false;
        if ($sender instanceof Player) {
            if(!isset($args[0])){
                if (!$fsender->hasFaction()) {
                    $sender->sendMessage(Text::parse("command.be.in.faction"));
                    $this->sendUsage($sender);
                    return true;
                } else {
                    $faction = $fsender->getFaction();
                    $own = true;
                }
            } else {
                if (!($faction = Factions::getByName($args[0])) instanceof Faction) {
                    $sender->sendMessage("Faction not found!");
                    return true;
                }
                if($fsender->getFaction() === $faction) $own = true;
            }
        } else {
            if(!isset($args[0])) {
                $this->sendUsage($sender);
                return true;
            } else {
                if (!($faction = Factions::getByName($args[0])) instanceof Faction) {
                    $sender->sendMessage("Faction not found!");
                    return true;
                }
            }
        }

		// Verify
		if ($faction->getFlag(Flag::PERMANENT))
        {
            $sender->sendMessage(Text::parse("<i>This faction is designated as permanent, so you cannot disband it."));
            return true;
        }

		// Event
		$event = new FactionDisbandEvent($faction->getId(), $fsender);
        Server::getInstance()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) return true;

		// Merged Apply and Inform

		// Run event for each player in the faction
		foreach ($faction->getPlayers() as $player)
		{
           $event = new PlayerMembershipChangeEvent($player, Factions::getById(FactionsPE::FACTION_ID_NONE), PlayerMembershipChangeEvent::REASON_DISBAND);
           $player->setRole(Rel::RECRUIT);
           Server::getInstance()->getPluginManager()->callEvent($event);
		}

		// Inform
		$faction->sendMessage(Text::parse("%var0 disbanded your faction.", $sender->getDisplayName()));

		if ($own)
        {
            $sender->sendMessage(Text::parse("<i>You disbanded <h>%var0<i>." , $faction->getName()));
        }

		// Log
		if (Settings::get("logFactionDisband", true))
        {
            FactionsPE::get()->getLogger()->info(Text::parse("<i>The faction <h>%var0 <i>(<h>%var1<i>) was disbanded by <h>%var2<i>.", $faction->getName(), $faction->getId(), $fsender->getDisplayName()));
        }

		// Apply
		$faction->detach();
        return true;
    }

}