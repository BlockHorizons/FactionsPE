<?php
namespace factions\command\subcommand;


use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasFaction;
use factions\command\subcommand\childs\ClaimAutoChildCommand;
use factions\command\subcommand\childs\ClaimCircleChildcommand;
use factions\command\subcommand\childs\ClaimFillChildCommand;
use factions\command\subcommand\childs\ClaimOneChildCommand;
use factions\command\subcommand\childs\ClaimSquareChildCommand;
use factions\FactionsPE;

class UnclaimSubCommand extends Command
{

    /**
     * ClaimSubCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "unclaim", "Unclaim this plot", FactionsPE::UNCLAIM);
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());

        $this->addChild(new ClaimOneChildCommand($plugin, false));
        $this->addChild(new ClaimAutoChildCommand($plugin, false));
        $this->addChild(new ClaimFillChildCommand($plugin, false));
        $this->addChild(new ClaimSquareChildCommand($plugin, false));
        $this->addChild(new ClaimCircleChildcommand($plugin, false));
        //$this->addChild(new ClaimAllChildCommand($plugin));

        $this->addParameter(new Parameter("one|auto|fill|square|circle|all", new TypeString(), false, false, "one"));
    }

}