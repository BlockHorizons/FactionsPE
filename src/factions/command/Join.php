<?php
namespace factions\command;


use dominate\Command;
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;

use factions\entity\Faction;
use factions\flag\Flag;
use factions\entity\Member;
use factions\manager\Members;
use factions\event\member\MembershipChangeEvent;
use factions\utils\Gameplay;
use factions\utils\Text;
use factions\FactionsPE;
use factions\relation\Relation;
use factions\command\parameter\MemberParameter;
use factions\command\parameter\FactionParameter;
use factions\command\requirement\FactionRequirement;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use localizer\Localizer;

class Join extends Command {

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases = []) {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

            $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
            $this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
            
            $this->addParameter(new FactionParameter("faction"));
            $this->addParameter((new MemberParameter("player", MemberParameter::ONLINE_MEMBER))->setDefaultValue("self"));
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     * @return BOOL
     * @throws \Exception
     */
    public function execute(CommandSender $sender, $label, array $args) {
        if (!parent::execute($sender, $label, $args)) return false;

        $msender = Members::get($sender);
        $mplayer = $this->getArgument(1);
        $samePlayer = ($msender === $mplayer);
        $faction = $this->getArgument(0);

        if(($ml = Gameplay::get("faction.member-limit", 10)) > 0 && count($faction->getMembers()) >= $ml){
            $sender->sendMessage(Localizer::translatable("faction-member-limit-exceeded", [$faction->getName(), $ml, $mplayer->getDisplayName()]));
            return true;
        }

        if (!Gameplay::get("can-join-with-negative-power", false) && $mplayer->getPower() < 0) {
            $sender->sendMessage(Localizer::translatable("faction-join-with-negative-power", [
                "who" => $samePlayer ? "You" : $mplayer->getDisplayName()
                ]));
            return true;
        }

        if (!$faction->getFlag(Flag::OPEN) && !$faction->isInvited($mplayer) && !$msender->isOverriding()) {
            $sender->sendMessage(Localizer::translatable("faction-requires-invitations"));
            if (!$samePlayer) {
                $faction->sendMessage(Localizer::translatable("member-tried-join-inform-faction", [$sender->getDisplayName()]));
            }
            return true;
        }

        $event = new MembershipChangeEvent($mplayer, $faction, MembershipChangeEvent::REASON_JOIN);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if($event->isCancelled()) return true;
        if (!$samePlayer) {
            $mplayer->sendMessage(Localizer::translatable("faction-joined-by-other", [$msender->getDisplayName(), $faction->getName()]));
        } else {
            $mplayer->sendMessage(Localizer::translatable("new-member-join-inform-self", [$faction->getName()]));
        }

        $faction->sendMessage(Localizer::translatable("new-member-join-inform-faction", [$mplayer->getDisplayName()]));
        if(!$samePlayer) {
            $msender->sendMessage(Localizer::translatable("new-member-join-inform-other", [$mplayer->getDisplayName(), $faction->getName()]));
        }

        // add to faction
        # TODO: Rewrite this
        $mplayer->resetFactionData();
        $mplayer->setFaction($faction);
        $faction->addMember($mplayer, Relation::RECRUIT);
        // remove invitation
        $faction->setInvited($mplayer, false);
        $mplayer->updateFaction();

        if (Gameplay::get("log.member-join", true)) {
            if ($samePlayer) {
                FactionsPE::get()->getLogger()->info(Localizer::trans("log.player-joined-faction", [$mplayer->getName(), $faction->getName()]));
            } else {
                FactionsPE::get()->getLogger()->info(Localizer::trans("log.player-joined-faction-by-other", [$msender->getDisplayName(), $mplayer->getName(), $faction->getName()]));
            }
        }
        return true;
    }
}