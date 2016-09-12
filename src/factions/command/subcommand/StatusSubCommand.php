<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\objs\Factions;
use factions\utils\Collections;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class StatusSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "status", "*** TODO ***", FactionsPE::STATUS, [], []);
    }
    
    public function execute(CommandSender $sender, $label, array $args) : BOOL {
        if (!parent::execute($sender, $label, $args)) return true;
        echo "Hello";
        // Prepare arguments
        if (!$sender instanceof Player) {
            // Player
            if(!isset($args[1])) return false;
            if(!isset($args[0])) return false;
            if (!(($faction = Factions::getByName($args[1])) instanceof Faction)) {
                $sender->sendMessage("Faction not found!");
                return true;
            }
            $page = 1;
            $sortBy = "time";
        } else {
            $fplayer = FPlayer::get($sender);
            if (isset($args[0])) {
                $page = $args[1];
            } else {
                $page = 0;
            }
            if (isset($args[0])) {
                if (!(($faction = Factions::getByName($args[0])) instanceof Faction)) {
                    $sender->sendMessage("Faction with name '$args[0]' not found got $faction!");
                    return true;
                }
            } else {
                $faction = $fplayer->getFaction();
                if (!$fplayer->hasFaction()) {
                    $sender->sendMessage("You have to be in faction.");
                    return true;
                }
            }
            $sortBy = "time";
            if (isset($args[1])) {
                $sortBy = $args[1];
            }
        }
        echo "Sort";

        // Sort
        /**
         * @var FPlayer[] $players
         */
        $players = $faction->getPlayers();
        $players = Collections::sort($players, $sortBy, "time");
        // Format the list
        $book = [];
        foreach ($players as $player) {
            // Name
            $displayName = $player->getDisplayName();
            $length = 15 - strlen($displayName);
            $length = $length < 1 ? $length = 1 : $length;
            $space = str_repeat(" ", $length);

            // Power
            $power = $player->getPower();
            $maxPower = $player->getPowerMax();
            $percent = $power / $maxPower;

            if ($percent > 0.75) $color = TextFormat::GREEN;
            elseif ($percent > 0.5) $color = TextFormat::YELLOW;
            elseif ($percent > 0.25) $color = TextFormat::RED;
            else $color = TextFormat::DARK_RED;

            if(!$player->isOnline()) {
                $lastActiveMillis = time() - $player->getLastPlayed();
            } else {
                $lastActiveMillis = time() - $player->getLastActivityMillis();
            }

            $power = Text::parse("<art>Power: $color{$power}/<gray>$maxPower<white>", $color, $power, $maxPower);

            $lastActive = ($player->isOnline() ? "<lime>Online right now." : "<i>Last active: " . date("*** TODO ***", $lastActiveMillis));

            $book[] = Text::parse("$displayName{$space} $power $lastActive", $displayName, $space, $power, $lastActive)."\n";
        }
        $book = array_chunk($book, 5);
        $page = (count($book) > $page) ? count($page) + 1 : $page;
        $page = $page < 0 ? $page = 1 : $page;
        $page = isset($book[$page-1]) ? $book[$page-1] : $book[0];
        
        foreach($page as $line){
            $sender->sendMessage($line);
        }

        return true;

    }

}