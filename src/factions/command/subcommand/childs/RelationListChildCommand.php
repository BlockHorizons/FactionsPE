<?php
namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use evalcore\entity\CPlayer;
use factions\command\parameter\type\TypeFaction;
use factions\command\parameter\type\TypeRel;
use factions\entity\Faction;
use factions\FactionsPE;
use factions\objs\Rel;
use factions\utils\Pager;
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class RelationListChildCommand extends Command
{

    const RELEVANT_RELATIONS = [Rel::ENEMY, Rel::TRUCE, Rel::ALLY];
    const SEPERATOR = (TextFormat::GRAY).": ";

    /**
     * RelationListChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "list", "See faction relations", FactionsPE::RELATION_LIST, ["ls"]);

        $this->addParameter(new Parameter("page", new TypeInteger(), false, false, 1));
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, false, "me"));
        $this->addParameter(new Parameter("relations", new TypeRel(), false, false, "all"));
    }

    /**
     * @param CommandSender $sender
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, $label, array $args) : bool
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        $page = $this->getParameter("page")->getValue();
        /**
         * @var Faction $faction
         */
        $faction = $this->getParameter("faction")->getValue();
        $relations = $this->getParameter("relations")->getValue();
        if(!is_array($relations)) $relations = [$relations];
        foreach([Rel::LEADER, Rel::OFFICER, Rel::MEMBER, Rel::RECRUIT] as $r) unset($relations[array_search($r, $relations)]);

        $pager = new Pager("", $page, 8, [], function(string $item, int $index, CommandSender $sender){
            return $item;
        }, $sender);
        // Finish pager properties
        $items = [];
        foreach($relations as $rel) {
            $coloredName = Text::getRelationColor($rel) . ucfirst($rel);
            foreach($faction->getFactionsWhereRelation($rel) as $f) {
                $items[] =  $coloredName . self::SEPERATOR . $f->getName();
            }
        }
        $pager->setObjects($items);
        $pager->setHeader(Text::parse("<white>%var0's <green>Relations <a>(%var1)", $faction->getName(), count($items)));
        $pager->stringify();

        $sender->sendMessage($pager->getHeader());
        if($sender instanceof CPlayer) $sender->sendMessage($pager->getOutput());
        else foreach($pager->getOutput() as $l) $sender->sendMessage($l);

        return true;
    }
}