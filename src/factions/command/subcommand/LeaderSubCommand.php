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
use pocketmine\command\CommandSender;
use pocketmine\Player;
use factions\utils\Text;

class LeaderSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "leader", "Promote new leader of faction", FactionsPE::LEADER
        );
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
        $this->addRequirement(new ReqHasRank(Rel::LEADER));

        $this->addParameter(new Parameter("player", new TypeString()));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);
        $playerName = $this->getParameter("player")->getValue();
        if (!($target = $this->plugin->getServer()->getPlayerExact($playerName)) instanceof Player) {
            $sender->sendMessage("Player not online!");
            return true;
        }
        $ftarget = FPlayer::get($target);
        if ($ftarget->getFaction() !== $fplayer->getFaction()) {
            $sender->sendMessage("Player must be in your faction!");
            return true;
        }
        if($ftarget === $fplayer) {
            $sender->sendMessage(Text::parse("<orange>You can't promote yourself!"));
        }

        $fplayer->getFaction()->promoteNewLeader($ftarget);
        return true;

    }

}