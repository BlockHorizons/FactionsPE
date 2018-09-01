<?php
namespace factions\form;

use dominate\Command;
use factions\command\FactionCommand;
use factions\FactionsPE;
use factions\utils\Text;
use jojoe77777\FormAPI\FormAPI;
use localizer\Localizer;
use pocketmine\Player;

class FactionForm {

	private $factionButtons = [
		"settings",
		"claim",
		"map",
		"invite",
		"members",
		"leave",
		"disband",
		"commands",
		"seechunk",
		"admin",
	];

	private $settingsButtons = [
		"name",
		"description",
		"flag",
		"permission",
		"relation",
	];

	private $factionlessButtons = [
		"create",
		"map",
		"commands",
		"admin",
	];

	/** @var FactionCommand */
	private $command;

	/** @var FactionsPE */
	private $plugin;

	public function __construct(FactionsPE $plugin, FactionCommand $command) {
		$this->command = $command;
		$this->plugin  = $plugin;

		$this->flipArrays();
	}

	private function flipArrays() {
		$arrays = ["faction", "factionless", "settings"];
		foreach ($arrays as $name) {
			$var        = $name . "Buttons";
			$this->$var = array_flip($this->$var);
		}
	}

	public function factionForm(Player $player) {
		$fapi = $this->getFormAPI();
		// Behaviour
		$form = $fapi->createSimpleForm(function (Player $player, int $result = null) {
			if ($result !== null) {
				$button  = array_flip($this->factionButtons)[$result];
				$handler = $button . "Handler";

				// Execute handler
				if (method_exists($this, $handler)) {
					$this->$handler($player);
				} else {
					$this->plugin->getLogger()->error("Form error: handler for button '$button' does not exist");
				}
			}
		});
		// Title
		$form->setTitle(Localizer::trans("menu-title"));
		// Buttons
		foreach ($this->factionButtons as $name => $id) {
			$color = ($c = $this->getChild($name)) && $player->hasPermission($c->getPermission()) && $c->testRequirements($player, true) ?: "<gray>";
			$form->addButton(Text::parse($color ?? "") . Localizer::trans("button-" . $name));
		}
		// Show
		$form->sendToPlayer($player);
	}

	public function factionlessForm(Player $player) {
		$fapi = $this->getFormAPI();
		// Behaviour
		$form = $fapi->createSimpleForm(function (Player $player, int $result = null) {
			if ($result !== null) {
				$button  = array_flip($this->factionlessButtons)[$result]; // TODO: flip is necessary?
				$handler = $button . "Handler";

				// Execute handler
				if (method_exists($this, $handler)) {
					$this->$handler($player);
				} else {
					$this->getPlugin()->error("Form error: handler for button '$button' does not exist");
				}
			}
		});
		// Title
		$form->setTitle(Localizer::trans("menu-title"));
		// Buttons
		foreach ($this->factionlessButtons as $name => $id) {
			$color = ($c = $this->getChild($name)) && $player->hasPermission($c->getPermission()) && $c->testRequirements($player, true) ?: "<gray>";
			$form->addButton(Text::parse($color ?? "") . Localizer::trans("button-" . $name));
		}
		// Show
		$form->sendToPlayer($player);
	}

	public function commandListForm(Player $player) {

	}

	public function getFormAPI(): FormAPI {
		return $this->plugin->getFormAPI();
	}

	public function getChild(string $name):  ? Command {
		return $this->command->getChild($name);
	}

	// ---------------------------------------
	// HANDLER
	// ---------------------------------------

	public function createHandler(Player $player) {
		$this->getChild("create")->createForm($player);
	}

	public function descriptionHandler(Player $player) {
		$this->getChild("description")->descriptionForm($player);
	}

	public function mapHandler(Player $player) {
		$this->getChild("map")->execute($player, "", []);
	}

	public function commandsHandler(Player $player) {
		$this->commandListForm($player);
	}

	public function inviteHandler(Player $player) {
		$this->getChild("invite")->inviteForm($player);
	}

	public function leaveHandler(Player $player) {
		$this->getChild("leave")->leaveForm($player);
	}

	public function claimHandler(Player $player) {
		$this->getChild("claim")->claimForm($player);
	}

	public function unclaimHandler(Player $player) {
		$this->getChild("claim")->unclaimForm($player);
	}

	public function seechunkHandler(Player $player) {
		$this->getChild("seechunk")->execute($player, "", []);
	}

	public function adminHandler(Player $player) {
		$this->getChild("override")->execute($player, "", []);
	}

	public function disbandHandler(Player $player) {
		$this->getChild("disband")->disbandForm($player);
	}

}