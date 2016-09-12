<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeBoolean;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class OverrideSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "override", "Turn on overriding mode", FactionsPE::OVERRIDE, ["admin"]);

        $this->addParameter(new Parameter("on/off", new TypeBoolean(), false, false, "toggle"));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);
        $this->getParameter("on/off")->setDefaultValue($fplayer->isOverriding() ? "false" : "true");
        if(parent::execute($sender, $label, $args) === false) {
            return true;
        }
        $fplayer->setOverriding(($value = $this->getParameter("on/off")->getValue()));
        $sender->sendMessage(Text::parse("You %var0 overriding mode", $value ? "<green>enabled<white>" : "<red>disabled<white>"));
        FactionsPE::get()->getLogger()->info(Text::parse("%var0 %var1 overriding mode", $sender->getDisplayName(), $value ? "<green>enabled<white>" : "<red>disabled<white>"));

        $this->getParameter("on/off")->setDefaultValue("toggle");
        return true;
    }

}