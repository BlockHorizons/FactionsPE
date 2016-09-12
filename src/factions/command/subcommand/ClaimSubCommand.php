<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/25/16
 * Time: 9:06 PM
 */

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

class ClaimSubCommand extends Command
{

    /**
     * ClaimSubCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "claim", "Claim this plot", FactionsPE::CLAIM);
        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasFaction());

        $this->addChild(new ClaimOneChildCommand($plugin, true));
        $this->addChild(new ClaimAutoChildCommand($plugin, true));
        $this->addChild(new ClaimFillChildCommand($plugin, true));
        $this->addChild(new ClaimSquareChildCommand($plugin, true));
        $this->addChild(new ClaimCircleChildcommand($plugin, true));
        //$this->addChild(new ClaimAllChildCommand($plugin));

        $this->addParameter(new Parameter("one|auto|fill|square|circle|all", new TypeString(), false, false, "one"));
    }

}