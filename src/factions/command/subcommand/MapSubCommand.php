<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeBoolean;
use evalcore\requirement\ReqBePlayer;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Collections;
use factions\utils\Constants;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MapSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        echo "Constructing...\n";
        parent::__construct($plugin, "map", "Show Factions map", FactionsPE::MAP);
        echo "Parent done\n";
        $this->addRequirement(new ReqBePlayer());
        echo "Added requirement\n";
        $this->addParameter(new Parameter("auto-update", new TypeBoolean(), false, false));
        echo "Done!\n";
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        if (isset($args[0])) {
            $val = $args[0];
            $fsender = FPlayer::get($sender);
            if ($val) {
                $fsender->setMapAutoUpdating(true);
                $fsender->sendMessage(Text::parse("command.map.auto.update.enabled"));
            } else {
                if($fsender->isMapAutoUpdating()) {
                    $fsender->setMapAutoUpdating(false);
                    $sender->sendMessage(Text::parse("command.map.auto.update.disabled"));
                }
            }
            return true;
        }

          
        /** @var Player $sender $map */
        $map = Collections::getMap($sender, Constants::MAP_WIDTH, Constants::MAP_HEIGHT, $sender->getYaw());

        foreach ($map as $line) {
            $sender->sendMessage($line);
        }

        return true;
    }


}