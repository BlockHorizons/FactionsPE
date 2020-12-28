<?php

namespace fpe\form;

use fpe\dominate\Command;
use fpe\command\FactionCommand;
use fpe\FactionsPE;
use fpe\utils\Text;
use jojoe77777\FormAPI\FormAPI;
use fpe\localizer\Localizer;
use pocketmine\Player;

class FactionForm
{

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

    public function __construct(FactionsPE $plugin, FactionCommand $command)
    {
        $this->command = $command;
        $this->plugin = $plugin;

        $this->flipArrays();
    }

    private function flipArrays()
    {
        $arrays = ["faction", "factionless", "settings"];
        foreach ($arrays as $name) {
            $var = $name . "Buttons";
            $this->$var = array_flip($this->$var);
        }
    }

    public function factionForm(Player $player)
    {
        // Make sure player is in a faction

        $fapi = $this->getFormAPI();
        // Behaviour
        $form = $fapi->createSimpleForm(function (Player $player, int $result = null) {
            if ($result !== null) {
                $button = array_flip($this->factionButtons)[$result];
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
        $this->setButtons($form, $this->factionButtons, $player);
        // Show
        $form->sendToPlayer($player);
    }

    public function getFormAPI(): FormAPI
    {
        return $this->plugin->getFormAPI();
    }

    public function setButtons($form, array $buttons, Player $player)
    {
        foreach ($buttons as $name => $id) {
            $form->addButton($this->getButton($name, $player));
        }
    }

    public function getButton(string $name, Player $player): string
    {
        $color = (($c = $this->getChild($name))
        && $player->hasPermission($c->getPermission())
        && $c->testRequirements($player, true) ? "" : "<lightgray>");
        return Text::parse($color) . Localizer::trans("button-" . $name);
    }

    public function getChild(string $name): ?Command
    {
        return $this->command->getChild($name);
    }

    public function factionlessForm(Player $player)
    {
        // TODO: Check if player is actually factionless

        $fapi = $this->getFormAPI();
        // Behaviour
        $form = $fapi->createSimpleForm(function (Player $player, int $result = null) {
            if ($result !== null) {
                $button = array_flip($this->factionlessButtons)[$result]; // TODO: flip is necessary?
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
        $this->setButtons($form, $this->factionlessButtons, $player);
        // Show
        $form->sendToPlayer($player);
    }

    public function createHandler(Player $player)
    {
        $this->getChild("create")->createForm($player);
    }

    // ---------------------------------------
    // HANDLER
    // ---------------------------------------

    public function descriptionHandler(Player $player)
    {
        $this->getChild("description")->descriptionForm($player);
    }

    public function mapHandler(Player $player)
    {
        $this->getChild("map")->execute($player, "", []);
    }

    public function commandsHandler(Player $player)
    {
        $this->commandListForm($player);
    }

    public function commandListForm(Player $player)
    {

    }

    public function settingsHandler(Player $player)
    {
        $fapi = $this->getFormAPI();
        //$form = $fapi->
    }

    public function inviteHandler(Player $player)
    {
        $this->getChild("invite")->inviteForm($player);
    }

    public function leaveHandler(Player $player)
    {
        $this->getChild("leave")->leaveForm($player);
    }

    public function claimHandler(Player $player)
    {
        $this->getChild("claim")->claimForm($player);
    }

    public function unclaimHandler(Player $player)
    {
        $this->getChild("claim")->unclaimForm($player);
    }

    public function seechunkHandler(Player $player)
    {
        $this->getChild("seechunk")->execute($player, "", []);
    }

    public function adminHandler(Player $player)
    {
        $this->getChild("override")->execute($player, "", []);
    }

    public function disbandHandler(Player $player)
    {
        $this->getChild("disband")->disbandForm($player);
    }

}