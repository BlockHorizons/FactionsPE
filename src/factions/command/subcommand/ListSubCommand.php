<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use evalcore\entity\CPlayer;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\utils\Pager;
use factions\utils\Text;
use pocketmine\command\CommandSender;

class ListSubCommand extends Command
{
	// -------------------------------------------- //
	// CONSTRUCT
	// -------------------------------------------- //
	
	public function __construct(FactionsPE $plugin)
	{
		parent::__construct($plugin, "list", "Show list of created factions", FactionsPE::LIST, ["ls"]);
		// Parameters
		$this->addParameter(new Parameter("page", new TypeInteger(), false, false, 1));
	}

	// -------------------------------------------- //
	// OVERRIDE
	// -------------------------------------------- //
	
	public function execute(CommandSender $sender, $label, array $args)
	{
		if(!parent::execute($sender, $label, $args)) return true;

		// Args
		$page = $this->getParameter("page")->getValue();
		//$fsender = FPlayer::get($sender);
		//final Predicate<MPlayer> onlinePredicate = PredicateAnd.get(SenderColl.PREDICATE_ONLINE, PredicateVisibleTo.get(sender));
		
		// NOTE: The faction list is quite slow and mostly thread safe.
		// We run it asynchronously to spare the primary server thread.
		$pageNumber = (int)min(count(Factions::getAll()), $page);
            if ($pageNumber < 1) {
                $pageNumber = 1;
            }
		$pager = new Pager(Text::titleize("<green>Faction list <gold>%var0/%var1"), $pageNumber, 5, Factions::getAll(), function(Faction $faction, int $index, CommandSender $sender){
			if ($faction->isNone())
			{
				return Text::parse("<i>Factionless<i> %var0 online", count($faction->getPlayersWhereOnline(true)));
			}
			else
			{
				return Text::parse("%var6%var0<i> %var1/%var2 online, %var3/%var4/%var5",
					$faction->getName(),
					count($faction->getPlayersWhereOnline(true)),
					count($faction->getPlayers()),
					$faction->getLandCount(),
					$faction->getPowerRounded(),
					$faction->getPowerMaxRounded(),
					$faction->getColorTo(FPlayer::get($sender))
				);
			}
		}, $sender);
		$pager->stringify();
		$sender->sendMessage(Text::parse($pager->getHeader(), $pager->getPage(), $pager->getMax()));
		if ($sender instanceof CPlayer) {
			$sender->sendMessage($pager->getOutput());
		} else {
			foreach($pager->getOutput() as $l) $sender->sendMessage($l);
		}

        return true;
	}

	public function factionToString(Faction $faction, int $index) {

	}
	
}