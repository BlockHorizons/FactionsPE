<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/12/16
 * Time: 10:29 PM
 */

namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeBoolean;
use evalcore\EvalCore;
use factions\command\parameter\type\TypeFaction;
use factions\command\parameter\type\TypePerm;
use factions\command\parameter\type\TypeRel;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\event\faction\FactionPermChangeEvent;
use factions\FactionsPE;
use factions\objs\Rel;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PermSetChild extends Command
{

    public function __construct(FactionsPE $plugin) {
        parent::__construct($plugin, "set", "Set faction permission", FactionsPE::PERM_SET, ["s"]);
        $this->addParameter(new Parameter("perm", new TypePerm()));
        $this->addParameter(new Parameter("rel", new TypeRel()));
        $this->addParameter(new Parameter("yes/no", new TypeBoolean()));
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, false, "me"));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) return true;

        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);
        /** @var Perm $perm */
        $perm = $this->getParameter("perm")->getValue();
        /** @var string $rel */
        $rel = $this->getParameter("rel")->getValue();
        /** @var bool $val */
        $val = $this->getParameter("yes/no")->getValue();
        /** @var Faction $faction */
        $faction = $this->getParameter("faction")->getValue();

        if($faction->isNone() and !$fplayer->isOverriding()) {
            $sender->sendMessage(Text::parse("<red>You can't modify permissions of special factions unless you're in admin mode."));
            return true;
        }

        if(!Perm::getPermById(Perm::PERMS)->has($fplayer, $faction)) return true;

        if( !$fplayer->isOverriding() || !$perm->isEditable() ) {
            $sender->sendMessage(Text::parse("<b>The perm <h>%var0 <b>is not editable.", $perm->getName()));
            return true;
        }
        $event = new FactionPermChangeEvent($sender, $faction, $perm, $rel, $val);
        EvalCore::callEvent($event);
        if($event->isCancelled()) return true;
        $newVal = $event->getNewValue();

        // no change
        if($faction->isPermitted($perm, $rel) === $newVal) {
            $sender->sendMessage(Text::parse("%var0 <i>already has %var1 <i>set to %var2 <i>for %var3<i>.", $faction->getName(), $perm->getDesc(), Text::parse($newVal ? "<g>YES" : "<b>NOO"), Text::getRelationColor($rel) . $rel));
            return true;
        }

        // Apply
        $faction->setRelationPermitted($perm, $rel, $newVal);
        if($perm === Perm::getPermById(Perm::PERMS) && in_array(Rel::LEADER, Perm::getPermById(Perm::PERMS)->getStandard(), true)) {
            $faction->setRelationPermitted(Perm::getPermById(Perm::PERMS), Rel::LEADER, true);
        }
        $messages = [];
        $messages[] = Text::titleize("Perm for " . $faction->getName());
        $messages[] = $perm->getStateHeaders();
        $messages[] = Text::parse($perm->getStateInfo($faction->getPermitted($perm), true));
        $sender->sendMessage($messages);
        $recipients = $faction->getPlayers();
        unset($recipients[array_search($fplayer, $recipients)]);
        foreach($recipients as $p) {
            $p->sendMessage(Text::parse("<h>%var0 <i>set a perm for <h>%var1<i>.", $sender->getDisplayName(), $faction->getName()));
        }
        return true;
    }
}