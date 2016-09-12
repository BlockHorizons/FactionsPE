<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\requirement\ReqBePlayer;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class InviteListSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "list", "See all invited players in your faction", FactionsPE::INVITE_LIST, [], []
        );
        $this->addRequirement(new ReqBePlayer());
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);
        $faction = $fplayer->getFaction();

        $iPlayers = $faction->getInvitedPlayers();
        if(empty($iPlayers)) {
        	$sender->sendMessage(Text::parse('command.invite.list.empty'));
        	return true;
        }
        $sender->sendMessage(Text::parse("command.invite.list.output", rtrim(implode(", ", $iPlayers), ', ') ));
        return true;
    }
}
