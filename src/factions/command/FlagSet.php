<?php
namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use factions\command\parameter\FactionParameter;
use factions\flag\Flag;
use factions\manager\Flags;
use factions\manager\Members;
use factions\utils\Pager;
use pocketmine\command\CommandSender;

class FlagSet extends Command {

	public function setup() {
		$this->addParameter(new FlagParameter("flag"));
		$this->addParameter(new Parameter("value"));
		$this->addParameter(new FactionParameter("faction")->setDefaultValue("me"));
	}

	public function perform(CommandSender $sender, $label, array $args) {
		// Args

		$flag = $this->getArgument(0);
		$value = $this->getArgument(1);
		$faction = $this->getArgument(2);

		// Do the sender have the right to change flags for this faction?
		if (!($p = Permissions::getById(Permission::FLAGS))->has($member, $faction)) {
			return ["requirement.faction-permission-error", [
            'perm_desc' => $p->getDescription(), 'faction' => $faction->getName()]];
		}

		if (!msender.isOverriding() && ! flag.isEditable()) {
			msg("<b>The flag <h>%s <b>is not editable.", flag.getName());
			return;
		}

		// Event
		$event = new EventFactionsFlagChange(sender, faction, flag, value);
		event.run();

		if ($event->isCancelled()) return;

		$value = $event->getNewValue();
		

		// No change 
		if ($faction->getFlag($flag) === $value) {
			return ["%s <i>already has %s <i>set to %s<i>.", [faction.describeTo(msender), flag.getStateDesc(value, false, true, true, false, true), flag.getStateDesc(value, true, true, false, false, false)]];
		}

		// Apply
		$faction->setFlag($flag, $value);


		// Inform
		$stateInfo = $flag->getStateDesc($faction->getFlag(flag), true, false, true, true, true);
		if (msender.getFaction() != faction)
		{
			// Send message to sender
			msg("<h>%s <i>set a flag for <h>%s<i>.", msender.describeTo(msender, true), faction.describeTo(msender, true));
			message(stateInfo);
		}
		faction.msg("<h>%s <i>set a flag for <h>%s<i>.", msender.describeTo(faction, true), faction.describeTo(faction, true));
		faction.sendMessage(stateInfo);
	}

}
