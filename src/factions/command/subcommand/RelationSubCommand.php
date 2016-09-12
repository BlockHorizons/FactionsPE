<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use factions\command\subcommand\childs\RelationListChildCommand;
use factions\command\subcommand\childs\RelationSetChildCommand;
use factions\command\subcommand\childs\RelationWishesChildCommand;
use factions\FactionsPE;

class RelationSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "relation", "Manage relations", FactionsPE::RELATION);

        //$this->addRequirement(self::REQ_AT_LEAST_ARGS, 1);

        $this->addChild(new RelationListChildCommand($plugin));
        $this->addChild(new RelationSetChildCommand($plugin));
        $this->addChild(new RelationWishesChildCommand($plugin));

        $this->addParameter(new Parameter("list|set|wishes", new TypeString(), false));
    }

}