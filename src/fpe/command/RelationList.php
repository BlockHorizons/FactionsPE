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
use fpe\command\parameter\RelationParameter;
use fpe\entity\Faction;
use fpe\manager\Factions;
use fpe\utils\Pager;
use localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use fpe\relation\Relation as Rel;

class RelationList extends Command
{

    const RELEVANT_RELATIONS = [Rel::ENEMY, Rel::TRUCE, Rel::ALLY];
    const SEPERATOR = TextFormat::GRAY.": ";


    public function setup()
    {
        // Parameter
        $this->addParameter((new RelationParameter("relations", RelationParameter::ANY))->setDefaultValue("all"));
        $this->addParameter((new Parameter("page", Parameter::TYPE_INTEGER))->setDefaultValue(1));
        $this->addParameter((new FactionParameter("faction|member", true))->setDefaultValue("self"));
    }

        public function perform(CommandSender $sender, $label, array $args)
        {
            // Args
            /** @var int */
            $page = $this->getArgument(1);
            /** @var Faction $faction */
            $faction = $this->getArgument(2);
            /** @var string[]|string $relations */
            $relations = $this->getArgument(0);

            if(!is_array($relations)) {
                if(!in_array($relations, self::RELEVANT_RELATIONS, true)) {
                    $sender->sendMessage(Localizer::translatable("cant-list-relation", [
                        "relation" => $relations
                    ]));
                } else {
                    $relations = [$relations];
                }
            } else {
                foreach ($relations as $rel) {
                    if(!in_array($rel, self::RELEVANT_RELATIONS, true)) {
                        unset($relations[array_search($rel, $relations, true)]);
                    }
                }
            }

            $pass = [];
            foreach ($faction->getRelationWishes() as $id => $wish) {
                if(in_array($wish, $relations, true)) {
                    if(($f = Factions::getById($id))) {
                        if($f->getRelationTo($faction) !== $wish) continue;
                    } else {
                        continue;
                    }
                    $pass[] = [
                        "relation" => $wish,
                        "faction" => $f->getName()
                    ];
                }
            }

            $pager = new Pager("relation-list-header", $page, $sender instanceof ConsoleCommandSender ? 20 : 5, $pass, $sender, function ($item) {
                return Rel::getColor($item["relation"]) . $item["relation"] . RelationList::SEPERATOR . $item["faction"];
            });
            $pager->sendTitle($sender, ['faction' => $faction->getName()]);


            foreach ($pager->getOutput() as $line) {
                $sender->sendMessage($line);
            }
            return true;
        }


}