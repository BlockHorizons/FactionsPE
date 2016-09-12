<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\requirement\ReqHasPerm;
use factions\entity\FPlayer;
use factions\entity\Perm;
use factions\FactionsPE;
use factions\objs\Plots;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;

class SetHomeSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct(
            $plugin, "sethome", "Set Faction home to your location", FactionsPE::SETHOME
        );

        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());
        $this->addRequirement(new ReqHasPerm(Perm::getPermById(Perm::SETHOME)));
        //$this->addRequirement(self::REQ_NOT_MORE_THAN_ARGS, 4);
        $this->addParameter(new Parameter("player|x", new TypeString(), false, false));
        $this->addParameter(new Parameter("y", new TypeString(), false, false));
        $this->addParameter(new Parameter("z", new TypeString(), false, false));
    }

    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }
        /** @var Player $sender */
        $fplayer = FPlayer::get($sender);

        $position = $sender->getPosition();

        if (count($args) === 1) {
            if (($target = $this->plugin->getServer()->getPlayer($args[0])) instanceof Player) {
                $position = $target->getPosition();
            } else {
                $sender->sendMessage(Text::parse('command.sethome.invalid.player'));
                return true;
            }
        } elseif (count($args) === 3) {
            if (!is_numeric($args[0]) || !is_numeric($args[1]) || !is_numeric($args[2])) {
                $this->sendUsage($sender);
                return true;
            }
            $position = new Position($args[0], $args[1], $args[2]);
            if (isset($args[3])) {
                if (($level = $this->getPlugin()->getServer()->getLevelByName($args[3])) instanceof Level) {
                    $position->level = $level;
                } else {
                    $sender->sendMessage(Text::parse('command.sethome.invalid.level', $args[3]));
                    return true;
                }
            } else {
                $position->level = $sender->getLevel();
            }
        }
        // validate position
        # TODO
        $factionHere = Plots::get()->getFactionAt($position);

        // sethome
        if(!$fplayer->getFaction()->isValidHome($position)) {
            $fplayer->sendMessage(Text::parse('command.sethome.not.here', $factionHere->getName()));
            return true;
        }
        $fplayer->getFaction()->setHome($position);

        $fplayer->sendMessage(Text::parse('command.sethome.success',
            "(" . $position->x . ", " . $position->y . ", " . $position->z . ", " . $position->level->getName()));
        return true;
    }

}