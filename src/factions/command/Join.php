<?php
namespace factions\command\subcommand;


use dominate\Command;
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;

use factions\entity\Faction;
use factions\entity\Flag;
use factions\entity\Member;
use factions\manager\Members;
use factions\event\member\MembershipChangeEvent;
use factions\utils\Gameplay;
use factions\utils\Text;
use factions\FactionsPE;

use pocketmine\command\CommandSender;
use pocketmine\Player;


class JoinSubCommand extends Command
{
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "join", "Join to a faction", FactionsPE::JOIN);

            $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
            $this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
            
            $this->addParameter(new FactionParameter("faction"));
    }
    /**
     * @param CommandSender $sender
     * @param array $args
     * @return BOOL
     * @throws \Exception
     */
    public function execute(CommandSender $sender, $label, array $args) {
        if (!parent::execute($sender, $label, $args)) return false;

        if(!isset($args[1])){
            $player = $sender;
        } else {
            if (!($player = $this->getPlugin()->getServer()->getPlayer($args[1])) instanceof Player) {
                $sender->sendMessage(Localizer::trans("Player not found"));
                return true;
            }
        }
        $samePlayer = $player === $sender;
        $fsender = Members::get($sender);
        $fplayer = Members::get($player);
        $faction = $this->getArgument(0);
        if(($ml = Gameplay::get("faction-member-limit", 10)) > 0 && count($faction->getPlayers()) >= $ml){
            $sender->sendMessage(Text::parse("<b>!<white> The faction %var0 is at limit of %var1 members, so %s cannot currently join", $faction->getName(), Settings::get("factionMemberLimit", 10), $fplayer->getDisplayName()));
            return true;
        }
        if (!Settings::get("canJoinWithNegativePower", false) && $fplayer->getPower()) {
            $sender->sendMessage(Text::parse(($samePlayer ? "You" : $player->getDisplayName())." cannot join a faction with a negative power level."));
            return true;
        }
        if ( !$faction->getFlag(Flag::OPEN) && !$faction->isInvited($fplayer->getName()) && !$fsender->isOverriding()) {
            $sender->sendMessage(Text::parse("<i>This faction requires invitations to join"));
            if ($samePlayer) {
                $faction->sendMessage(Text::parse("%var0 tried to join your faction.", $sender->getDisplayName()));
            }
            return true;
        }
        $event = new PlayerMembershipChangeEvent($fplayer, $faction, PlayerMembershipChangeEvent::REASON_JOIN);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if($event->isCancelled()) return true;
        if (!$samePlayer)
        {
            $fplayer->sendMessage(Text::parse("<i>%var0 <i>moved you into the faction %var1<i>.", $fsender->getDisplayName(), $faction->getName()));
        }
        $faction->sendMessage(Text::parse("<i>%var0 <i>joined <lime>your faction<i>.", $fplayer->getDisplayName()));
        $fsender->sendMessage(Text::parse("<i>%var0 <i>successfully joined %var1<i>.", $fplayer->getDisplayName()), $faction->getName());
        // Apply
        /** @var Faction $faction */
        $fplayer->resetFactionData();
        $fplayer->setFaction($faction);
        $faction->setInvited($fplayer->getName(), false);
        if (Settings::get("logFactionJoin", true)) {
            if ($samePlayer) {
                FactionsPE::get()->getLogger()->info(Text::parse("%var0 joined the faction %var1.", $fplayer->getName(), $faction->getName()));
            } else {
                FactionsPE::get()->getLogger()->info(Text::parse("%var0 moved the player %var1 into the faction %var2.", $fsender->getDisplayName(), $fplayer->getName(), $faction->getName()));
            }
        }
        return true;
    }
}