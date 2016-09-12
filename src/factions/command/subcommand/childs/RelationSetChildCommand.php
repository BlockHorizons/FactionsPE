<?php
namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use factions\command\parameter\type\TypeRel;
use factions\command\parameter\type\TypeFaction;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class RelationSetChildCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "set", "Set relation status with other faction", FactionsPE::RELATION_SET, ["s"]);

        //$this->addRequirement(self::REQ_NOT_MORE_THAN_ARGS, 2);
        $this->addParameter(new Parameter("faction", new TypeFaction(), false));
        $this->addParameter(new Parameter("relation", (new TypeRel(true))->allowAll(true), false));
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOl
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        $faction = $this->getParameter("faction")->getValue();
        $relation = $this->getParameter("relation")->getValue();

        return true;
    }
    
}