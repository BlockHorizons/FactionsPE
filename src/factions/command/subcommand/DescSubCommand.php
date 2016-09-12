<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasRank;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\objs\Rel;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class DescSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "description", "factions.command.description", FactionsPE::DESCRIPTION, ["desc"]);

        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
        $this->addRequirement(new ReqHasRank(Rel::LEADER));

        $this->addParameter(new Parameter("...description", new TypeString(), false));
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOL
    {
        /** @var Player $sender */
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        $desc = implode(" ", $args);
        $fsender = FPlayer::get($sender);
        $fsender->getFaction()->setDescription($desc);
        $fsender->getFaction()->sendMessage(Text::parse('command.desc.set', $sender->getDisplayName(), $desc));
        return true;
    }

}