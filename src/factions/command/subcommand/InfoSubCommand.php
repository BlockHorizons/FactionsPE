<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/20/16
 * Time: 8:42 PM
 */

namespace factions\command\subcommand;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use factions\command\parameter\type\TypeFaction;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\objs\Plots;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class InfoSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct(
            $plugin, "info", "Fetch faction info", "factions.info", []
        );

        $this->addParameter(new Parameter("faction", new TypeFaction(), true));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        if (!isset($args[0])) {
            if ($sender instanceof Player) {
                $fplayer = FPlayer::get($sender);
                $faction = $fplayer->getFaction();
            } else {
                $sender->sendMessage(Text::parse("command.be.player") . " Or use '/f info <faction>'");
                return true;
            }
        } else {

            if (!($faction = Factions::getByName($args[0])) instanceof Faction) {
                $sender->sendMessage(Text::parse('command.info.faction.not.found', $args[0]));
                return true;
            }

        }

        # Let's pull some info from the faction
        $head = Text::parse('command.info.header', $faction->getName());
        $motd = Text::parse('command.info.motd', ($d = $faction->getDescription()) == "" ? "~" : $d);
        $id = Text::parse('command.info.id', $faction->getId()); # etc..
        $plots = Text::parse('command.info.plots', count(Plots::get()->getFactionPlots($faction)));
        # TODO: Relationship with $sender faction...
        $members = Text::parse('command.info.members', count($faction->getPlayers()));
        if($faction->getLeader()) {
            $leader = Text::parse('command.info.leader', $faction->getLeader()->getDisplayName());
        } else {
            $leader = Text::parse('command.info.leader', "N/A");
        }
        $bank = Text::parse('command.info.bank', 0); // faction money
        $power = Text::parse('command.info.power', $faction->getPower());

        foreach ([$head, $motd, $id, $plots, $members, $leader, $bank, $power] as $line) {
            $sender->sendMessage($line);
        }

        return true;
    }

}