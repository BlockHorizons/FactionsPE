<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.6.4
 * Time: 00:43
 */

namespace fpe\command;


use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\entity\Faction;
use fpe\manager\Factions;
use fpe\utils\Pager;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use fpe\relation\Relation as Rel;

class RelationWishes extends Command
{

    public function setup()
    {
        // Parameter
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new FactionParameter("faction|member", true))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        # TODO
        # ---------
        # ally: safezone, warzone
        # enemy: vandamee, uno, fifa, legendz
        # truce: epicgamer

        // Args
        /** @var int */
        $page = $this->getArgument(0);
        /** @var Faction $faction */
        $faction = $this->getArgument(1);

        $pass = [];
        foreach ($faction->getRelationWishes() as $id => $wish) {
            if(($f = Factions::getById($id))) {
                if($f->getRelationTo($faction) === $wish) {
                    // Wish is not a wish anymore if both have at least equal "wishes"
                    continue;
                } else {
                    $pass[] = [
                        "faction" => $f->getName(),
                        "relation" => $wish
                    ];
                }
            } else {
                // No longer existing faction
                continue;
            }
        }

        $pager = new Pager("relation-list-header", $page, $sender instanceof ConsoleCommandSender ? 20 : 5, $pass, $sender, function ($item) {
            return Rel::getColor($item["relation"]) . $item["relation"] . RelationList::SEPERATOR . $item["faction"];
        });
        $pager->stringify();
        
        $pager->sendTitle($sender, ["faction" => $faction->getName()]);

        foreach ($pager->getOutput() as $line) {
            $sender->sendMessage($line);
        }
        return true;
    }


}