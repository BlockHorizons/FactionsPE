<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/19/16
 * Time: 11:49 PM
 */

namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasntFaction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\integrations\Economy;
use factions\objs\Factions;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class CreateSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "create", "Create new faction", FactionsPE::CREATE, ["new"], []);
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasntFaction());

        $this->addParameter(new Parameter("name", new TypeString(), false));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);

        if (($price = Economy::get()->getPrice('faction.create')) > ($money = Economy::get()->getMoney($fplayer->getPlayer()))) {
            $sender->sendMessage(Text::parse('command.create.money.prob', $price, $money));
            return true;
        }
        if (!(ctype_alnum($args[0]))) {
            $sender->sendMessage(Text::parse("command.faction.name.wrong.format"));
            return true;
        }
        if (Text::isNameBanned($args[0])) {
            $sender->sendMessage(Text::parse("command.faction.name.banned"));
            return true;
        }
        if (Factions::getByName($args[0])) {
            $sender->sendMessage(Text::parse("command.create.faction.exists"));
            return true;
        }
        if (strlen($args[0]) > Settings::get('faction.name.length', 8)) {
            $sender->sendMessage("This name is too long. Please try again!");
            return true;
        }
        $name = $args[0];

        Factions::create($name, $fplayer);
        //FPlayer::updatePlayerTag($sender);

        $sender->sendMessage(Text::parse("command.create.success", $name));
        return true;
    }
}