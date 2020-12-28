<?php

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\localizer\Localizer;
use pocketmine\command\CommandSender;

class FlagSet extends Command
{

    public function setup()
    {
        $this->addParameter(new FlagParameter("flag"));
        $this->addParameter(new Parameter("value"));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Args

        $flag = $this->getArgument(0);
        $value = $this->getArgument(1);
        $faction = $this->getArgument(2);
        $msender = Members::get($sender);

        // Do the sender have the right to change flags for this faction?
        if (!($p = Permissions::getById(Permission::FLAGS))->has($msender, $faction)) {
            return ["requirement.faction-permission-error", [
                'perm_desc' => $p->getDescription(), 'faction' => $faction->getName()]];
        }

        if (!$msender->isOverriding() && !$flag->isEditable()) {
            return ["<b>The flag <h>%s <b>is not editable.", ["flag" => $flag->getName()]];
        }

        // Event
        $event = new EventFactionsFlagChange($sender, $faction, $flag, $value);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);

        if ($event->isCancelled()) return true;

        $value = $event->getNewValue();


        // No change
        if ($faction->getFlag($flag) === $value) {
            return ["%s <i>already has %s <i>set to %s<i>.", [$faction->getName(), $flag->getStateDesc(value, false, true, true, false, true), $flag->getStateDesc(value, true, true, false, false, false)]];
        }

        // Apply
        $faction->setFlag($flag, $value);


        // Inform
        $stateInfo = $flag->getStateDesc($faction->getFlag($flag), true, false, true, true, true);
        if ($msender->getFaction() != $faction) {
            // Send message to sender
            $msender->sendMessage(Localizer::translatable("<h>%s <i>set a flag for <h>%s<i>.", [
                $msender->getDisplayName(),
                $faction->describeTo($msender, true)
            ]));
            $msender->sendMessage($stateInfo);
        }
        $faction->sendMessage(Localizer::trans("<h>%s <i>set a flag for <h>%s<i>.", [
            $msender->getDisplayName(),
            $faction->getName()
        ]));
        $faction->sendMessage($stateInfo);

        return true;
    }

}
